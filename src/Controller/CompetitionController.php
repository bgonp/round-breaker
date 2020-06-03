<?php

namespace App\Controller;

use App\Entity\Player;
use App\Repository\CompetitionRepository;
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use App\Repository\RegistrationRepository;
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
                $this->addFlash('error', 'Required field name can\'t be empty');
            } else {
                $description = $request->request->get('description');
                $game = $gameRepository->find($request->request->get('game'));
                $playersPerTeam = $request->request->get('individual') ? 1 : $request->request->get('playersPerTeam');
                $teamNum = $request->request->get('teamNum');
                if ($playersPerTeam > 5 || $playersPerTeam < 1) {
                    $playersPerTeam = 1;
                }
                if (!is_int(log($teamNum, 2)) || $teamNum < 2 || $teamNum > 16) {
                    $teamNum = 2;
                }
                $competition = (new Competition())
                    ->setName($name)
                    ->setDescription($description)
                    ->setIsOpen(true)
                    ->setIsFinished(false)
                    ->setStreamer($player)
                    ->setMaxPlayers($playersPerTeam * $teamNum)
                    ->setGame($game)
                    ->setPlayersPerTeam($playersPerTeam);
                $competitionRepository->save($competition);
                return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
            }
        }
        return $this->render('competition/new.html.twig', [
            'games' => $gameRepository->findAll()
        ]);
    }

    /** @Route("/toggle_confirmation", name="toggle_confirmation", methods={"POST"}) */
    public function toggleConfirmation(
        Request $request,
        Player $player,
        Competition $competition,
        RegistrationRepository $registrationRepository
    ): Response {
        $registration = $registrationRepository->findOneByPlayerAndCompetition($player, $competition);
        if ($registration && $request->request->get('confirm')=="1") {
            $registration->setIsConfirmed(true);
        } else {
            $registration->setIsConfirmed(false);
        }
        $registrationRepository->save($registration);
        return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
    }

    /**
     * @Route("/delete", name="competition_delete", methods={"POST"})
     */
    public function delete(Competition $competition, CompetitionRepository $competitionRepository) {
        if ($this->isGranted('ROLE_ADMIN') || $competition->getStreamer()->equals($this->getUser())) {
            $competitionRepository->remove($competition);
            $this->addFlash('success', 'Competition removed');
        }
        return $this->redirectToRoute('main');
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
        /** @var Player $player */
        $player = $this->getUser();
        $playerIsStreamer = $player ? $competition->getStreamer()->equals($player) : false;
        return $this->render('competition/show.html.twig', [
            'competition' => $competition,
            'player'=> $player,
            'registration' => $registrationRepository->findOneByPlayerAndCompetition($player, $competition),
            'clickable' => false,
            'createStreamerButtons' => $playerIsStreamer || $this->isGranted('ROLE_ADMIN'),
            'createRegistrationButtons' => $competition->getIsOpen() && $player,
            'createRandomizeButton' => $playerIsStreamer || $this->isGranted('ROLE_ADMIN')
        ]);
    }

    /**
     * @Route("/{id}/edit", name="competition_edit", methods={"GET", "POST"})
     */
<<<<<<< HEAD
    public function edit(Request $request, CompetitionRepository $competitionRepository): Response {
=======
    public function editCompetition(
        Request $request,
        CompetitionRepository $competitionRepository,
        GameRepository $gameRepository
    ) {
>>>>>>> master
        $competition = $competitionRepository->findCompleteById($request->get('id'));
        $player = $competition->getStreamer();
        $showKick = ($competition->getIsOpen() && !$competition->getIsFinished());
        $showConfirm = !$competition->getIsFinished();
        if (!$this->isGranted('ROLE_ADMIN') && (!$this->getUser() || $this->getUser()->getUsername() !== $player->getUsername())) {
            return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
        }
<<<<<<< HEAD
        if ($request->isMethod('POST')) {
            if (!($name = $request->request->get('name'))) {
                $this->addFlash('error', 'Required field name can\'t be empty');
            } else {
                $competition->setName($request->request->get('name'));
                $competition->setDescription($request->request->get('description'));
                $competition->setIsOpen($request->request->get('open') ? true : false);
                $competition->setIsFinished($request->request->get('finished') ? true : false);
                $competitionRepository->save($competition);
            }
=======
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
>>>>>>> master
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
<<<<<<< HEAD
    public function viewBracket(int $id, CompetitionRepository $competitionRepository): Response {
        $competition = $competitionRepository->findCompleteById($id);
=======
    public function viewCompetitionBracket(
        Request $request,
        Competition $competition,
        PlayerRepository $playerRepository,
        CompetitionRepository $competitionRepository,
        TeamRepository $teamRepository
    ) {
>>>>>>> master
        $isStreamer = $competition->getStreamer()->equals($this->getUser());
        if ($isStreamer) {
            $competition = $competitionRepository->findCompleteById($request->get('id'));
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
            ($competition->getStreamer()->equals($this->getUser()) || $this->isGranted('ROLE_ADMIN'))
        ) {
            $teamService->randomize($competition);
        } else {
            $this->addFlash('error', 'You cannot edit this competition or it isn\'t open');
        }
        return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
    }

    /**
     * @Route("/fill", name="competition_fill", methods={"POST"})
     */
    public function fillTeams(Competition $competition, TeamService $teamService): Response
    {
        if (
            !$competition->getIsOpen() &&
            ($competition->getStreamer()->equals($this->getUser()) || $this->isGranted('ROLE_ADMIN'))
        ) {
            $teamService->fillTeams($competition);
        } else {
            $this->addFlash('error', 'You cannot edit this competition or it is still open');
        }
        return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
    }
}
