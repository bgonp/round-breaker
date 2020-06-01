<?php

namespace App\Controller;

use App\Entity\Competition;
use App\Entity\Game;
use App\Repository\CompetitionRepository;
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Service\CompetitionService;
use App\Service\GameService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/game")
 */
class GameController extends AbstractController
{

    /**
     * @Route("/new", name="game_new", methods={"GET", "POST"})
     */
    public function createGame(
        Request $request,
        GameRepository $gameRepository,
        GameService $gameService)
    {
        if ($request->request->has('name')) {
            $game = $gameRepository->findOneBy(['name' => $request->request->get('name')]);
            if (!$game) {
                $gameService->createGame($request->request->get('name'), $request->request->get('description'));
            }
            return $this->redirectToRoute('main');
        } else {
            return $this->render('game/new.html.twig', [
                'controller_name' => 'CompetitionController',
                'games' => $gameRepository->findAll()
            ]);
        }
    }

    /**
     * @Route("/delete", name="game_delete", methods={"POST"})
     */
    public function deleteGame(
        Request $request,
        GameRepository $gameRepository
    ) {
        if ($request->request->has('id')) {
            $game = $gameRepository->findOneBy(['id' => $request->request->get('id')]);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($game);
            $entityManager->flush();
        }
        return $this->redirectToRoute('main');
    }

    /**
     * @Route("/{id}/edit", name="game_edit", methods={"GET", "POST"})
     */
    public function editGame(
        Request $request,
        Game $game,
        GameRepository $gameRepository
    ) {
        if ($this->isGranted("ROLE_ADMIN")) {
            if ($request->request->has('name')) {
                $game->setName($request->request->get('name'));
                $game->setDescription($request->request->get('description'));
                $gameRepository->save($game);
            }
            return $this->render('game/edit.html.twig', [
                'controller_name' => 'GameController',
                'game' => $game
            ]);
        } else {
            return $this->redirectToRoute('main');
        }
    }

    /**
     * @Route("/{id}", name="game_show", methods={"GET"})
     */
    public function viewGame(
        Game $game,
        CompetitionRepository $competitionRepository,
        PlayerRepository $playerRepository
    ) {
        $user = $this->getUser();
        return $this->render('game/show.html.twig', [
            'controller_name' => 'GameController',
            'game' => $game,
            'competitions' => $competitionRepository->findBy(['game' => $game]),
            'player'=> $this->getUser() ? $playerRepository->findOneBy(["username" => $this->getUser()->getUsername()]) : null
        ]);
    }
}