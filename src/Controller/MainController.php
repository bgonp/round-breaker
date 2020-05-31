<?php

namespace App\Controller;

use App\Repository\CompetitionRepository;
use App\Repository\GameRepository;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Game;
use App\Entity\Competition;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main", methods={"GET"})
     */
    public function main(GameRepository $gameRepository, CompetitionRepository $competitionRepository): Response
    {
        $user = $this->getUser();
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'games' => $gameRepository->findAll(),
            'competitions' => $competitionRepository->findAll(),
            'createCompetitionButton' => $user !== null,
            'createGameButton' => $this->isGranted('ROLE_ADMIN')
        ]);
    }
}