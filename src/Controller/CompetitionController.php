<?php

namespace App\Controller;

use App\Entity\Player;
use App\Entity\Team;
use App\Repository\CompetitionRepository;
use App\Repository\GameRepository;
use App\Repository\RegistrationRepository;
use App\Repository\TeamRepository;
use App\Service\TeamService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Competition;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/competition")
 */
class CompetitionController extends AbstractController
{
    /**
     * @Route("/", name="competition_list", methods={"GET"})
     */
    public function index(
        CompetitionRepository $competitionRepository,
        RegistrationRepository $registrationRepository
    ): Response {
        /** @var Player $player */
        $player = $this->getUser();
        return $this->render('competition/index.html.twig', [
            'competitions' => $competitionRepository->findAll(),
            'canEditGame' => $this->isGranted('ROLE_ADMIN'),
            'player'=> $player,
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
            $this->redirectToRoute('main');
        }
        if ($request->isMethod('POST')) {
            if (!($name = $request->request->get('name'))) {
                $this->addFlash('error', 'El campo nombre es obligatorio');
            } else if (!($heldAt = new \DateTime($request->request->get('heldAt')))) {
                $this->addFlash('error', 'El campo fecha y hora es obligatorio');
            } else {
                $playersPerTeam = $request->request->get('playersPerTeam');
                $teamNum = $request->request->get('teamNum');
                $competition = (new Competition())
                    ->setName($name)
                    ->setDescription($request->request->get('description'))
                    ->setIsOpen(true)
                    ->setIsFinished(false)
                    ->setStreamer($player)
                    ->setMaxPlayers($teamNum * $playersPerTeam)
                    ->setGame($gameRepository->find($request->request->get('game')))
                    ->setPlayersPerTeam($playersPerTeam)
                    ->setHeldAt($heldAt);
                if ($previousCompetition = $competitionRepository->findLastByStreamer($player)) {
                    $competition
                        ->setTwitchChannel($previousCompetition->getTwitchChannel())
                        ->setTwitchBotName($previousCompetition->getTwitchBotName())
                        ->setTwitchBotToken($previousCompetition->getTwitchBotToken());
                }
                $competitionRepository->save($competition);
                return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
            }
        }
        return $this->render('competition/new.html.twig', [
            'games' => $gameRepository->findAll()
        ]);
    }

    /**
     * @Route("/{id}", name="competition_show", methods={"GET"})
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
        if ($competition->getStreamer()->equals($player)) {
            return $this->redirectToRoute('competition_edit', ['id' => $competition->getId()]);
        }
        return $this->render('competition/show.html.twig', [
            'competition' => $competition,
            'showRegistrationButton'=> $player !== null,
            'playerRegistration' => $player ? $registrationRepository->findOneByPlayerAndCompetition($player, $competition) : null,
            'clickable' => false,
            'showEditButtons' => $this->isGranted('ROLE_ADMIN')
        ]);
    }

    /**
     * @Route("/{id}/edit", name="competition_edit", methods={"GET", "POST"})
     */
    public function edit(
        Request $request,
        Competition $competition,
        CompetitionRepository $competitionRepository,
        GameRepository $gameRepository
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN') && !$competition->getStreamer()->equals($this->getUser())) {
            return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
        }
        if ($request->isMethod('POST')) {
            if (!($name = $request->request->get('name'))) {
                $this->addFlash('error', 'El campo nombre es obligatorio');
            } else {
                if ($competition->getIsOpen()) {
                    $playersPerTeam = $request->request->get('individual') ? 1 : $request->request->get('playersPerTeam');
                    $teamNum = $request->request->get('teamNum');
                    $competition
                        ->setMaxPlayers($playersPerTeam * $teamNum)
                        ->setPlayersPerTeam($playersPerTeam)
                        ->setGame($gameRepository->find($request->request->get('game')))
                        ->setHeldAt(new \DateTime($request->request->get('heldAt')));
                }
                $competition
                    ->setName($request->request->get('name'))
                    ->setDescription($request->request->get('description'))
                    ->setIsOpen($request->request->get('open') ? true : false)
                    ->setIsFinished($request->request->get('finished') ? true : false);
                $competitionRepository->save($competition);
            }
        }
        return $this->render('competition/edit.html.twig', [
            'games' => $gameRepository->findAll(),
            'competition' => $competition,
            'clickable' => true
        ]);
    }

    /**
     * @Route("/delete", name="competition_delete", methods={"POST"})
     */
    public function delete(Competition $competition, CompetitionRepository $competitionRepository) {
        if ($this->isGranted('ROLE_ADMIN') || $competition->getStreamer()->equals($this->getUser())) {
            $competitionRepository->remove($competition);
        }
        return $this->redirectToRoute('main');
    }

    /**
     * @Route("/{id}/bracket", name="competition_bracket", methods={"GET"})
     */
    public function viewBracket(int $id, CompetitionRepository $competitionRepository): Response {
        $competition = $competitionRepository->findCompleteById($id);
        $isStreamer = $competition->getStreamer()->equals($this->getUser());
        if ($isStreamer) {
            return $this->render('competition/bracket.html.twig', [
                'competition' => $competition
            ]);
        }
        return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
    }

    /**
     * @Route("/randomize", name="competition_randomize", methods={"POST"})
     */
    public function randomizeTeams(Competition $competition, TeamService $teamService): Response
    {
        if (
            $competition->getIsOpen() &&
            ($this->isGranted('ROLE_ADMIN') || $competition->getStreamer()->equals($this->getUser()))
        ) {
            if (!$teamService->randomize($competition)) {
                $this->addFlash('error', 'No hay suficientes jugadores confirmados');
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
            $teamService->replacePlayer($team, $player);
        }
        return $this->redirectToRoute('competition_edit', ['id' => $team->getCompetition()->getId()]);
    }
}
