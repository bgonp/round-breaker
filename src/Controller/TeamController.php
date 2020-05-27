<?php

namespace App\Controller;

use App\Entity\Competition;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Service\CompetitionService;
use App\Service\TeamService;
use App\Service\PlayerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Player;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
    /**
     * @Route("/competition/{id}/randomize", name="competition_randomize")
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