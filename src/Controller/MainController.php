<?php

namespace App\Controller;

use App\Repository\CompetitionRepository;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Game;
use App\Entity\Competition;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main", methods={"GET"})
     */
    public function main()
    {
        $entityManager = $this->getDoctrine()->getManager();
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'games' => $entityManager->getRepository(Game::Class)->findAll(),
            'competitions' => $entityManager->getRepository(Competition::Class)->findAll()
        ]);
    }
}