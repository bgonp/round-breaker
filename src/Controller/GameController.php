<?php

namespace App\Controller;

use App\Repository\GameRepository;
use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class GameController extends AbstractController
{
    /**
     * @Route("/game/new", name="game_new", methods={"POST"})
     */
    public function createGame(
        Request $request,
        GameRepository $gameRepository,
        GameService $gameService)
    {
        if ($request->request->has('name')) {
            $game =$gameRepository->findOneBy(['name' => $request->request->get('name')]);
            if (!$game) {
                $gameService->createGame($request->request->get('name'), $request->request->get('description'));
            }
            return $this->redirectToRoute('main');
        } else {
            return $this->render('main/createGame.html.twig', [
                'controller_name' => 'MainController',
                'games' => $gameRepository->findAll()
            ]);
        }
    }
}