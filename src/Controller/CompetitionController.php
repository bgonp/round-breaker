<?php

namespace App\Controller;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Team;
use App\Exception\NotEnoughConfirmedRegistrationsException;
use App\Repository\CompetitionRepository;
use App\Repository\GameRepository;
use App\Repository\RegistrationRepository;
use App\Repository\RoundRepository;
use App\Service\CompetitionService;
use App\Service\TeamService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/competition")
 */
class CompetitionController extends AbstractController
{
    /**
     * @Route("/page/{page<\d+>}", name="competition_list", methods={"GET"})
     */
    public function index(
        CompetitionRepository $competitionRepository,
        RegistrationRepository $registrationRepository,
        GameRepository $gameRepository,
        int $page = 0
    ): Response {
        $perPage = 20;
        $lastPage = (int) ceil($competitionRepository->count([]) / $perPage);
        $currentPage = $page < 1 ? 1 : ($page > $lastPage ? $lastPage : $page);
        if ($currentPage !== $page) {
            return $this->redirectToRoute('competition_list', ['page' => $currentPage]);
        }

        /** @var Player $player */
        $player = $this->getUser();

        return $this->render('competition/index.html.twig', [
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'lastPage' => $lastPage,
            'competitions' => $competitionRepository->findAllOrdered($page, $perPage),
            'canEditGame' => $this->isGranted('ROLE_ADMIN'),
            'player' => $player,
            'games' => $gameRepository->findAll(),
            'registrations' => $player ? $registrationRepository->findOpenByPlayer($player) : [],
        ]);
    }

    /**
     * @Route("/new", name="competition_new", methods={"GET", "POST"})
     */
    public function new(
        Request $request,
        CompetitionRepository $competitionRepository,
        GameRepository $gameRepository
    ) {
        if (!($player = $this->getUser())) {
            $this->addFlash('error', 'No puedes crear una competición sin haber iniciado sesión');

            return $this->redirectToRoute('main');
        }
        if ($request->isMethod('POST')) {
            if (!($name = $request->request->get('name'))) {
                $this->addFlash('error', 'El campo nombre es obligatorio');
            } elseif (!($heldAt = new \DateTime($request->request->get('heldAt')))) {
                $this->addFlash('error', 'El campo fecha y hora es obligatorio');
            } else {
                $playersPerTeam = $request->request->get('playersPerTeam');
                $teamNum = $request->request->get('teamNum');
                $competition = (new Competition())
                    ->setName($name)
                    ->setDescription($request->request->get('description'))
                    ->setStreamer($player)
                    ->setMaxPlayers($teamNum * $playersPerTeam)
                    ->setGame($gameRepository->find($request->request->get('game')))
                    ->setPlayersPerTeam($playersPerTeam)
                    ->setHeldAt($heldAt)
                    ->setLobbyName($request->request->get('lobbyname'))
                    ->setLobbyPassword($request->request->get('lobbypassword'))
                    ;
                if ($previousCompetition = $competitionRepository->findLastByStreamer($player)) {
                    $competition
                        ->setTwitchChannel($previousCompetition->getTwitchChannel())
                        ->setTwitchBotName($previousCompetition->getTwitchBotName())
                        ->setTwitchBotToken($previousCompetition->getTwitchBotToken());
                }
                $competitionRepository->save($competition);

                return $this->redirectToRoute('competition_edit', ['id' => $competition->getId()]);
            }
        }

        return $this->render('competition/new.html.twig', [
            'games' => $gameRepository->findAllOrdered(),
        ]);
    }

    /**
     * @Route("/{id<\d+>}", name="competition_show", methods={"GET"})
     */
    public function show(
        int $id,
        CompetitionRepository $competitionRepository,
        RegistrationRepository $registrationRepository
    ): Response {
        $competition = $competitionRepository->findCompleteById($id);
        if (!$competition) {
            $this->addFlash('error', 'No existe competición con ese ID');

            return $this->redirectToRoute('competition_list');
        }
        /** @var Player $player */
        $player = $this->getUser();
        if ($player && $competition->getStreamer()->equals($player)) {
            return $this->redirectToRoute('competition_edit', ['id' => $competition->getId()]);
        }

        return $this->render('competition/show.html.twig', [
            'competition' => $competition,
            'showRegistrationButton' => $competition->getIsOpen(),
            'playerRegistration' => $player ? $registrationRepository->findOneByPlayerAndCompetition($player, $competition) : null,
            'clickable' => false,
            'showEditButtons' => $this->isGranted('ROLE_ADMIN'),
            'bracketType' => count($competition->getRounds()) < 1 ? 0 : $competition->getTeams()->count(),
        ]);
    }

    /**
     * @Route("/{id<\d+>}/edit", name="competition_edit", methods={"GET", "POST"})
     */
    public function edit(
        Request $request,
        int $id,
        CompetitionRepository $competitionRepository,
        GameRepository $gameRepository,
        RoundRepository $roundRepository,
        RegistrationRepository $registrationRepository
    ): Response {
        $player = $this->getUser();
        $competition = $competitionRepository->findCompleteById($id);
        if (!$competition) {
            $this->addFlash('error', 'No existe competición con ese ID');

            return $this->redirectToRoute('competition_list');
        }
        if (!$this->isGranted('ROLE_ADMIN') && !$competition->getStreamer()->equals($this->getUser())) {
            $this->addFlash('error', 'No puedes editar competiciones de otos usuarios');

            return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
        }
        if ($request->isMethod('POST')) {
            if ($competition->getIsFinished()) {
                $this->addFlash('error', 'No puedes editar una competición finalizada');
            } elseif (!($name = $request->request->get('name'))) {
                $this->addFlash('error', 'El campo nombre es obligatorio');
            } else {
                $wasOpen = $competition->getIsOpen();
                $competition
                    ->setName($name)
                    ->setDescription($request->request->get('description'))
                    ->setIsOpen((bool) $request->request->get('open'));
                if ($wasOpen) {
                    $playersPerTeam = $request->request->get('playersPerTeam');
                    $teamNum = $request->request->get('teamNum');
                    $competition
                        ->setMaxPlayers($playersPerTeam * $teamNum)
                        ->setPlayersPerTeam($playersPerTeam)
                        ->setGame($gameRepository->find($request->request->get('game')))
                        ->setHeldAt(new \DateTime($request->request->get('heldAt')));
                }
                $competitionRepository->save($competition);
                if (!$wasOpen && $competition->getIsOpen()) {
                    $roundRepository->removeFromCompetition($competition);
                }
            }
            return $this->redirectToRoute('competition_edit', ['id' => $competition->getId()]);
        }

        return $this->render('competition/edit.html.twig', [
            'games' => $gameRepository->findAllOrdered(),
            'competition' => $competition,
            'clickable' => true,
            'showRegistrationButton' => $competition->getIsOpen(),
            'playerRegistration' => $registrationRepository->findOneByPlayerAndCompetition($player, $competition),
            'bracketType' => count($competition->getRounds()) < 1 ? 0 : $competition->getTeams()->count(),
            'randomize' => count($competition->getRounds()) < 1
        ]);
    }

    /**
     * @Route("/delete", name="competition_delete", methods={"POST"})
     */
    public function delete(Competition $competition, CompetitionRepository $competitionRepository)
    {
        if (!$this->isGranted('ROLE_ADMIN') && !$competition->getStreamer()->equals($this->getUser())) {
            $this->addFlash('error', 'No puedes borrar una competición de otro usuario');
        } else {
            $competitionRepository->remove($competition);
        }

        return $this->redirectToRoute('competition_list');
    }

    /**
     * @Route("/randomize", name="competition_randomize", methods={"POST"})
     */
    public function randomize(Competition $competition, CompetitionService $competitionService): Response
    {
        if (
            $competition->getIsOpen() &&
            ($this->isGranted('ROLE_ADMIN') || $competition->getStreamer()->equals($this->getUser()))
        ) {
            try {
                $competitionService->randomize($competition);
            } catch (NotEnoughConfirmedRegistrationsException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        } else {
            $this->addFlash('error', 'La competición esta cerrada o no tienes permisos para editar');
        }

        return $this->redirectToRoute('competition_edit', ['id' => $competition->getId()]);
    }

    /**
     * @Route("/kick_member", name="kick_member", methods={"POST"})
     */
    public function kickMember(Team $team, Player $player, TeamService $teamService): Response
    {
        if (
            $this->isGranted('ROLE_ADMIN') ||
            $team->getCompetition()->getStreamer()->equals($this->getUser())
        ) {
            if ($team->getCompetition()->getIsFinished()) {
                $this->addFlash('error', 'No puedes eliminar a un jugador de un equipo en una competición terminada');
            } else {
                try {
                    $teamService->replacePlayer($team, $player);
                } catch (NotEnoughConfirmedRegistrationsException $e) {
                    $this->addFlash('error', $e->getMessage());
                }
            }
        }

        return $this->redirectToRoute('competition_edit', ['id' => $team->getCompetition()->getId()]);
    }
}
