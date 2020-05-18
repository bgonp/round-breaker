<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Game;
use App\Entity\Player;
use App\Entity\Competition;
use Symfony\Component\HttpFoundation\Request;

class MainController extends AbstractController
{
    /**
     * @Route("/main", name="main")
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

    /**
     * @Route("/main/createGame", name="create_game")
     */
    public function createGame(Request $request)
    {

        if ($request->request->has('name')) {
            $em = $this->getDoctrine()->getManager();
            $game = $em->getRepository(Game::class)->findOneBy(['name' => $request->request->get('name')]);
            if (!$game) {
                $game = new Game();
                $game->setName($request->request->get('name'));
                $game->setDescription($request->request->get('description'));
                $em->persist($game);
                $em->flush();
            }
            return $this->redirectToRoute('main');
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            return $this->render('main/createGame.html.twig', [
                'controller_name' => 'MainController',
                'games' => $entityManager->getRepository(Game::Class)->findAll()
            ]);
        }
    }

    /**
     * @Route("/main/createCompetition", name="create_competition")
     */
    public function createCompetition(Request $request)
    {

        if ($request->request->has('name')) {
            $em = $this->getDoctrine()->getManager();
            $competition = $em->getRepository(Competition::class)->findOneBy(['name' => $request->request->get('name')]);
            if (!$competition) {
                $competition = new Competition();
                $competition->setName($request->request->get('name'));
                $competition->setDescription($request->request->get('description'));
                $competition->setIsOpen(true);
                $competition->setIsFinished(false);
                $competition->setGame($em->getRepository(Game::class)->findOneBy(['name' => $request->request->get('game')]));
                $em->persist($competition);
                $em->flush();
            }
            return $this->redirectToRoute('main');
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            return $this->render('main/createCompetition.html.twig', [
                'controller_name' => 'MainController',
                'games' => $entityManager->getRepository(Game::Class)->findAll(),
                'players' => $entityManager->getRepository(Player::Class)->findAll(),
                'competitions' => $entityManager->getRepository(Competition::Class)->findAll()
            ]);
        }
    }
}