<?php

namespace App\Controller;

use App\Repository\CompetitionRepository;
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Service\CompetitionService;
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
     * @Route("/", name="competition_list")
     */
    public function index(CompetitionRepository $competitionRepository): Response
    {
        return $this->render('main/viewCompetitionList.html.twig', [
            'controller_name' => 'CompetitionController',
            'competitions' => $competitionRepository->findAll()
        ]);
    }

    /**
     * @Route("/new", name="competition_new")
     */
    public function createCompetition(
        Request $request,
        CompetitionRepository $competitionRepository,
        GameRepository $gameRepository,
        PlayerRepository $playerRepository,
        CompetitionService $competitionService)
    {
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
            return $this->render('main/createCompetition.html.twig', [
                'controller_name' => 'CompetitionController',
                'games' => $gameRepository->findAll(),
                'competitions' => $competitionRepository->findAll()
            ]);
        }
    }

    /**
     * @Route("/join", name="competition_join", methods={"GET"})
     */
    public function joinCompetition(
        Request $request,
        PlayerRepository $playerRepository,
        CompetitionRepository $competitionRepository,
        CompetitionService $competitionService
    ) {
        if ($request->query->has('id')) {
            $player = $this->getUser()->getUsername();
            $player = $playerRepository->findOneBy(['username' => $player]);
            $competition = $competitionRepository->findOneBy(['id' => $request->query->get('id')]);
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
     * @Route("/delete", name="competition_delete", methods={"GET"})
     */
    public function deleteCompetition(
        Request $request,
        PlayerRepository $playerRepository,
        CompetitionRepository $competitionRepository,
        CompetitionService $competitionService
    ) {
        if ($request->query->has('id')) {
            $player = $this->getUser()->getUsername();
            $player = $playerRepository->findOneBy(['username' => $player]);
            $competition = $competitionRepository->findOneBy(['id' => $request->query->get('id')]);
            if ($competition && $competition->getStreamer() === $player) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($competition);
                $entityManager->flush();
            }
        }
        return $this->redirectToRoute('main');
    }

    /**
     * @Route("/{id}", name="competition_show")
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
        return $this->render('main/viewCompetition.html.twig', [
            'controller_name' => 'CompetitionController',
            'competition' => $competition,
            'teams' => $teams,
            'player'=> $playerRepository->findOneBy(["username" => $this->getUser()->getUsername()])
        ]);
    }

    /**
     * @Route("/{id}/edit", name="competition_edit", methods={"GET", "POST"})
     */
    public function editCompetition(
        Request $request,
        Competition $competition,
        PlayerRepository $playerRepository,
        CompetitionRepository $competitionRepository,
        TeamRepository $teamRepository,
        CompetitionService $competitionService
    ) {
        $player = $this->getUser()->getUsername();
        $player = $playerRepository->findOneBy(['username' => $player]);
        if ($player === $competition->getStreamer()) {
            if ($request->request->has('name')) {
                $competition->setName($request->request->get('name'));
                $competition->setDescription($request->request->get('description'));
                $competitionRepository->save($competition);
            }
            $teams = $teamRepository->findCompleteTeamsFromCompetition($competition);
            return $this->render('main/editCompetition.html.twig', [
                'controller_name' => 'CompetitionController',
                'competition' => $competition,
                'teams' => $teams
            ]);
        } else {
            return $this->redirectToRoute('competition_list');
        }
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
            return $this->render('main/viewCompetitionBracket.html.twig', [
                'controller_name' => 'CompetitionController',
                'competition' => $competition,
                'teams' => $teams
            ]);
        } else {
            return $this->redirectToRoute('competition_list');
        }
    }
}