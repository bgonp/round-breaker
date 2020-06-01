<?php

namespace App\Controller;

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
use Symfony\Component\Security\Core\User\UserInterface;

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
            'player'=> $isAuthed ? $playerRepository->findOneBy(["username" => $user->getUsername()]) : null,
            'game' => null,
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
                    $gameRepository->findOneBy(['name' => $request->request->get('game')])
                );
            }
            return $this->redirectToRoute('main');
        } else {
            return $this->render('competition/new.html.twig', [
                'controller_name' => 'CompetitionController',
                'games' => $gameRepository->findAll(),
                'competitions' => $competitionRepository->findAll()
            ]);
        }
    }

    /**
     * @Route("/registration_new", name="registration_new", methods={"POST"})
     */
    public function makeRegistration(
        Request $request,
        PlayerRepository $playerRepository,
        CompetitionRepository $competitionRepository,
        CompetitionService $competitionService
    ) {
        if ($request->request->has('id')) {
            $user = $this->getUser();
            $isAuthed = $user !== null;
            $player = $isAuthed ? $playerRepository->findOneBy(["username" => $user->getUsername()]) : null;
            $competition = $competitionRepository->findOneBy(['id' => $request->request->get('id')]);
            //$team = $em->getRepository(Team::class)->findOneBy(['name' => $request->request->get('team')]);
            if (/*$team &&*/ $competition && $competition->getIsOpen()) {
                $competitionService->addPlayerToCompetition($competition, $player);
            }
            return $this->redirectToRoute('competition_list');
        } else {
            return $this->redirectToRoute('main');
        }
    }

    /**
     * @Route("/registration_delete", name="registration_delete", methods={"POST"})
     */
    public function deleteRegistration(
        Request $request,
        PlayerRepository $playerRepository,
        CompetitionRepository $competitionRepository,
        RegistrationRepository $registrationRepository
    ) {
        if ($request->request->has('competitionId')) {
            $player = $playerRepository->findOneBy(['id' => $request->request->get('playerId')]);
            $competition = $competitionRepository->findOneBy(['id' => $request->request->get('competitionId')]);
            $registration = $registrationRepository->findOneBy(
                ['player' => $player,
                    'competition' => $competition]);
            //$team = $em->getRepository(Team::class)->findOneBy(['name' => $request->request->get('team')]);
            if (/*$team &&*/ $competition && $competition->getIsOpen() &&
                ($this->isGranted("ROLE_ADMIN")
                    || $player->getUsername() == $this->getUser()->getUsername()) && $registration) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($registration);
                $entityManager->flush();
            }
            return $this->redirectToRoute('competition_list');
        } else {
            return $this->redirectToRoute('main');
        }
    }

    /**
     * @Route("/delete", name="competition_delete", methods={"POST"})
     */
    public function deleteCompetition(
        Request $request,
        PlayerRepository $playerRepository,
        CompetitionRepository $competitionRepository,
        CompetitionService $competitionService
    ) {
        if ($request->request->has('id')) {
            $player = $this->getUser()->getUsername();
            $player = $playerRepository->findOneBy(['username' => $player]);
            $competition = $competitionRepository->findOneBy(['id' => $request->request->get('id')]);
            if ($competition && $competition->getStreamer() === $player) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($competition);
                $entityManager->flush();
            }
        }
        return $this->redirectToRoute('main');
    }

    /**
     * @Route("/{id}", name="competition_show", methods={"GET"})
     */
    public function viewCompetition(
        Request $request,
        Competition $competition,
        PlayerRepository $playerRepository,
        CompetitionRepository $competitionRepository,
        TeamRepository $teamRepository,
        CompetitionService $competitionService
    ) {
        $teams = $teamRepository->findCompleteTeamsFromCompetition($competition);
        $player = $this->getUser() ? $playerRepository->findOneBy(["username" => $this->getUser()->getUsername()]) : null;
        return $this->render('competition/show.html.twig', [
            'controller_name' => 'CompetitionController',
            'competition' => $competition,
            'teams' => $teams,
            'player'=> $player,
            'createStreamerButtons' => $competition->getStreamer() === $player or $this->isGranted('ROLE_ADMIN'),
            'createRegistrationButtons' => $competition->getIsOpen() && $player,
            'createRandomizeButton' => $competition->getIsIndividual() && $competition->getStreamer() === $player
        ]);
    }

    /**
     * @Route("/{id}/edit", name="competition_edit", methods={"GET", "POST"})
     */
    public function editCompetition(
        Request $request,
        Competition $competition,
        CompetitionRepository $competitionRepository,
        TeamRepository $teamRepository
    ) {
        $player = $competition->getStreamer();
        if (!$this->isGranted('ROLE_ADMIN') && (!$this->getUser() || $this->getUser()->getUsername() !== $player->getUsername())) {
            return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
        }
        if ($request->request->has('name')) {
            $competition->setName($request->request->get('name'));
            $competition->setDescription($request->request->get('description'));
            $competitionRepository->save($competition);
        }
        $teams = $teamRepository->findCompleteTeamsFromCompetition($competition);
        return $this->render('competition/edit.html.twig', [
            'controller_name' => 'CompetitionController',
            'competition' => $competition,
            'teams' => $teams
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
        $player = $this->getUser()->getUsername();
        $player = $playerRepository->findOneBy(['username' => $player]);
        if ($player === $competition->getStreamer()) {
            $teams = $teamRepository->findCompleteTeamsFromCompetition($competition);
            return $this->render('competition/bracket.html.twig', [
                'controller_name' => 'CompetitionController',
                'competition' => $competition,
                'teams' => $teams
            ]);
        } else {
            return $this->redirectToRoute('competition_list');
        }
    }

    /**
     * @Route("/randomize", name="competition_randomize", methods={"POST"})
     */
    public function randomizeTeams(
        Competition $competition,
        PlayerRepository $playerRepository,
        TeamService $teamService
    ): Response {
        $user = $this->getUser()->getUsername();
        $user = $playerRepository->findOneBy(['username' => $user]);
        $teamService->randomize($user, $competition);

        return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
    }
}