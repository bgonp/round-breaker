<?php

namespace App\Controller;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PlayerController extends AbstractController
{
    /**
     * @Route("/player/{id}", name="player_show")
     */
    public function viewPlayer(PlayerRepository $playerRepository, Player $player): Response
    {
        return $this->render('main/viewPlayer.html.twig', [
            'controller_name' => 'PlayerController',
            'player'=> $player,
        ]);
    }

    /**
     * @Route("/profile", name="profile", methods={"GET","POST"})
     */
    public function viewProfile(PlayerRepository $playerRepository): Response
    {
        return $this->render('main/viewProfile.html.twig', [
            'controller_name' => 'ProfileController',
            'player'=> $playerRepository->findOneBy(["username" => $this->getUser()->getUsername()]),
        ]);
    }
}