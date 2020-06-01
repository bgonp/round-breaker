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
     * @Route("/player/{id}", name="player_show", methods={"GET"})
     */
    public function viewPlayer(PlayerRepository $playerRepository, Player $player): Response
    {
        return $this->render('player/show.html.twig', [
            'controller_name' => 'PlayerController',
            'player'=> $player,
        ]);
    }

    /**
     * @Route("/profile", name="profile", methods={"GET","POST"})
     */
    public function viewProfile(PlayerRepository $playerRepository): Response
    {
        if (!($user = $this->getUser())) {
            return $this->redirectToRoute('main');
        }
        return $this->render('player/edit.html.twig', [
            'controller_name' => 'ProfileController',
            'player'=> $user ? $playerRepository->findOneBy(["username" => $user->getUsername()]) : null,
        ]);
    }
}