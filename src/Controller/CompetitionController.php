<?php

namespace App\Controller;

use App\Entity\Player;
use App\Repository\CompetitionRepository;
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use App\Repository\RegistrationRepository;
use App\Repository\TeamRepository;
use App\Service\CompetitionService;
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
    public function index(CompetitionRepository $competitionRepository, PlayerRepository $playerRepository): Response
    {
        $user = $this->getUser();
        $isAuthed = $user !== null; 
        return $this->render('competition/index.html.twig', [
            'competitions' => $competitionRepository->findAll(),
            'canEditGame' => $isAuthed && $this->isGranted('ROLE_ADMIN'),
            'player'=> $isAuthed ? $playerRepository->findOneBy(["username" => $user->getUsername()]) : null,
        ]);
    }

    /**
     * @Route("/new", name="competition_new", methods={"GET", "POST"})
     */
    public function createCompetition(
        Request $request,
        CompetitionRepository $competitionRepository,
        GameRepository $gameRepository,
        PlayerRepository $playerRepository,
        CompetitionService $competitionService
    ) {
        if ($request->request->has('name')) {
            $competition = $competitionRepository->findOneBy(['name' => $request->request->get('name')]);
            $user = $this->getUser()->getUsername();
            $user = $playerRepository->findOneBy(['username' => $user]);
            $playersPerTeam = $request->request->get('playersPerTeam');
            $teamNum = $request->request->get('teamNum');
            if ($playersPerTeam > 5 || $playersPerTeam < 1) {
                $playersPerTeam = 1;
            }
            if (!is_int(log($teamNum, 2)) || $teamNum < 2 || $teamNum > 16) {
                $teamNum = 2;
            }
            if (!$competition) {
                $competitionService->createCompetition(
                    $request->request->get('name'),
                    $request->request->get('description'),
                    $user,
                    ($playersPerTeam * $teamNum),
                    $request->request->get('individual') ? true : false,
                    $request->request->get('playersPerTeam'),
                    $gameRepository->findOneBy(['name' => $request->request->get('game')]),
                    $request->request->get('dateAndTime'),
                );
            }
            return $this->redirectToRoute('main');
        } else {
            return $this->render('competition/new.html.twig', [
                'games' => $gameRepository->findAll(),
                'competitions' => $competitionRepository->findAll()
            ]);
        }
    }

    /** @Route("/toggle_confirmation", name="toggle_confirmation", methods={"POST"}) */
    public function toggleConfirmation(
        Request $request,
        PlayerRepository $playerRepository,
        CompetitionRepository $competitionRepository,
        RegistrationRepository $registrationRepository
    ): Response {
        $competition = $competitionRepository->findOneBy(['id' => $request->request->get('competitionId')]);
        $player = $playerRepository->findOneBy(['id' => $request->request->get('playerId')]);
        $registration = $registrationRepository->findOneBy(['competition' => $competition, 'player' => $player]);
        if ($registration && $request->request->get('confirm')=="1") {
            $registration->setIsConfirmed(true);
        } else {
            $registration->setIsConfirmed(false);
        }
        $registrationRepository->save($registration);
        return $this->redirectToRoute('competition_show', array('id' => $request->request->get('competitionId')));
    }

    /**
     * @Route("/delete", name="competition_delete", methods={"POST"})
     */
    public function deleteCompetition(Competition $competition, CompetitionRepository $competitionRepository) {
        if ($competition && $competition->getStreamer()->equals($this->getUser())) {
            $competitionRepository->remove($competition);
        }
        return $this->redirectToRoute('main');
    }

    /**
     * @Route("/{id}", name="competition_show", methods={"GET"})
     */
    public function viewCompetition(
        Request $request,
        CompetitionRepository $competitionRepository
    ) {
        $competition = $competitionRepository->findCompleteById($request->get('id'));
        /** @var Player $player */
        $player = $this->getUser();
        $playerIsStreamer = $player ? $competition->getStreamer()->equals($player) : false;
        return $this->render('competition/show.html.twig', [
            'competition' => $competition,
            'player'=> $player,
            'clickable' => false,
            'createStreamerButtons' => $playerIsStreamer || $this->isGranted('ROLE_ADMIN'),
            'createRegistrationButtons' => $competition->getIsOpen() && $player,
            'createRandomizeButton' => !$competition->getIsIndividual() && ($playerIsStreamer || $this->isGranted('ROLE_ADMIN'))
        ]);
    }

    /**
     * @Route("/{id}/edit", name="competition_edit", methods={"GET", "POST"})
     */
    public function editCompetition(
        Request $request,
        CompetitionRepository $competitionRepository,
        GameRepository $gameRepository
    ) {
        $competition = $competitionRepository->findCompleteById($request->get('id'));
        $player = $competition->getStreamer();
        $showKick = ($competition->getIsOpen() && !$competition->getIsFinished());
        $showConfirm = !$competition->getIsFinished();
        if (!$this->isGranted('ROLE_ADMIN') && (!$this->getUser() || $this->getUser()->getUsername() !== $player->getUsername())) {
            return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
        }
        if ($request->request->has('name')) {
            $playersPerTeam = $request->request->get('playersPerTeam');
            $teamNum = $request->request->get('teamNum');
            $competition->setName($request->request->get('name'));
            $competition->setDescription($request->request->get('description'));
            $competition->setIsOpen($request->request->get('open') ? true : false);
            $competition->setIsFinished($request->request->get('finished') ? true : false);
            $competition->setMaxPlayers($playersPerTeam*$teamNum);
            $competition->setHeldAt(new \DateTime($request->request->get('dateAndTime')));
            $competitionRepository->save($competition);
        }
        return $this->render('competition/edit.html.twig', [
            'games' => $gameRepository->findAll(),
            'competition' => $competition,
            'clickable' => true,
            'showKick' => $showKick,
            'showConfirm' => $showConfirm,
        ]);
    }

    /**
     * @Route("/{id}/bracket", name="competition_bracket", methods={"GET"})
     */
    public function viewCompetitionBracket(
        Request $request,
        Competition $competition,
        PlayerRepository $playerRepository,
        CompetitionRepository $competitionRepository,
        TeamRepository $teamRepository
    ) {
        $isStreamer = $competition->getStreamer()->equals($this->getUser());
        if ($isStreamer) {
            $competition = $competitionRepository->findCompleteById($request->get('id'));
            return $this->render('competition/bracket.html.twig', [
                'competition' => $competition
            ]);
        }
        return $this->redirectToRoute('competition_list');
    }

    /**
     * @Route("/randomize", name="competition_randomize", methods={"POST"})
     */
    public function randomizeTeams(
        Request $request,
        TeamService $teamService,
        CompetitionRepository $competitionRepository
    ): Response {
        if ($request->request->has('id')) {
            $competition = $competitionRepository->findOneBy(['id' => $request->request->get('id')]);
            $teamService->randomize($this->getUser(), $competition);
            return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
        }

        return $this->redirectToRoute('competition_list');
    }
}
