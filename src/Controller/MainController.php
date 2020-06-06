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
        $competition = $competitionRepository->findOneRandomFinished();
        $competition = $competitionRepository->findCompleteById($competition->getId());

        return $this->render('main/index.html.twig', [
            'last_username' => $request->query->get('last_username'),
            'last_email' => $request->query->get('last_email'),
            'last_twitchname' => $request->query->get('last_twitchname'),
            'competition' => $competition,
            'clickable' => false,
            'player' => $this->getUser(),
            'mostsPlayed' => $gameRepository->findMostPlayed(),
            'bracketType' => $competition->getIsOpen() ? 0 : $competition->getTeams()->count(),
        ]);
    }

    /** @Route("/test") */
    public function test(CompetitionRepository $competitionRepository, PlayerRepository $playerRepository): Response
    {
        dd($competitionRepository->findWithRegistrationsAndTeamsByPlayer($playerRepository->find(1800)));
    }
}
