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
use Symfony\Component\Routing\Annotation\Route;

class CompetitionController extends AbstractController
{
    /**
     * @Route("/competition/new", name="competition_new")
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
                'controller_name' => 'MainController',
                'games' => $gameRepository->findAll(),
                'competitions' => $competitionRepository->findAll()
            ]);
        }
    }

    /**
     * @Route("/competition/join", name="competition_join")
     */
    public function joinCompetition(
        Request $request,
        CompetitionRepository $competitionRepository,
        PlayerRepository $playerRepository,
        CompetitionService $competitionService)
    {
        if ($request->request->has('competition')) {
            $player = $this->getUser()->getUsername();
            $player = $playerRepository->findOneBy(['username' => $player]);
            $competition = $competitionRepository->findOneBy(['name' => $request->request->get('competition')]);
            //$team = $em->getRepository(Team::class)->findOneBy(['name' => $request->request->get('team')]);
            if (/*$team &&*/ $competition && $competition->getIsOpen()) {
                $competitionService->addPlayerToCompetition($competition, $player);
            }
            return $this->redirectToRoute('main');
        } else {
            return $this->render('main/joinCompetition.html.twig', [
                'controller_name' => 'MainController',
                'competitions' => $competitionRepository->findAll(),
                'player' => $playerRepository->findOneBy(['username' => $this->getUser()->getUsername()])
            ]);
        }
    }

    /**
     * @Route("/competition/{id}", name="competition_show", methods={"GET"})
     */
    public function viewCompetition(
        Competition $competition,
        CompetitionRepository $competitionRepository,
        TeamRepository $teamRepository
    ) {
        $teams = $teamRepository->findCompleteTeamsFromCompetition($competition);
        return $this->render('main/viewCompetition.html.twig', [
            'controller_name' => 'MainController',
            'competition' => $competition,
            'teams' => $teams
        ]);
    }
}