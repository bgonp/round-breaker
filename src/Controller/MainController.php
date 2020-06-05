<?php

namespace App\Controller;

use App\Repository\CompetitionRepository;
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main", methods={"GET"})
     */
    public function main(
        Request $request,
        CompetitionRepository $competitionRepository,
        GameRepository $gameRepository
    ): Response {
        $randomCompetition = $competitionRepository->findOneRandomFinishedWithRoundsAndTeams();
        return $this->render('main/index.html.twig', [
            'last_username' => $request->get('last_username'),
            'last_email' => $request->get('last_email'),
            'last_twitchname' => $request->get('last_twitchname'),
            'competition' => $randomCompetition,
            'clickable' => false,
            'player' => $this->getUser(),
            'mostsPlayed' => $gameRepository->findMostsPlayed()
        ]);
    }

    /**
     * @Route("/test", name="test")
     */
    public function test(PlayerRepository $playerRepository, CompetitionRepository $competitionRepository): void
    {
        $competition = $competitionRepository->findLastByStreamer($playerRepository->find(357));
        dd($competition);
    }
}