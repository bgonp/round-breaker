<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Player;
use App\Repository\CompetitionRepository;
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use App\Repository\RegistrationRepository;
use App\Service\GameService;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/game")
 */
class GameController extends AbstractController
{
    /**
     * @Route("/", name="game_list", methods={"GET"})
     */
    public function index(GameRepository $gameRepository, PlayerRepository $playerRepository): Response
    {
        $user = $this->getUser();
        $isAuthed = $user !== null;
        return $this->render('game/index.html.twig', [
            'games' => $gameRepository->findAll(),
            'player'=> $isAuthed ? $playerRepository->findOneBy(["username" => $user->getUsername()]) : null
        ]);
    }

    /**
     * @Route("/new", name="game_new", methods={"GET", "POST"})
     */
    public function new(
        Request $request,
        GameRepository $gameRepository,
        GameService $gameService
    ): Response {
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
    public function delete(
        Request $request,
        GameRepository $gameRepository
    ): Response {
        if ($request->request->has('id')) {
            $game = $gameRepository->findOneBy(['id' => $request->request->get('id')]);
            $entityManager = $this->getDoctrine()->getManager();
            try {
                $entityManager->remove($game);
                $entityManager->flush();
            } catch (Exception $e) {
                return $this->redirectToRoute('game_show', array("id" => $request->request->get('id')));
            }
        }
        return $this->redirectToRoute('game_list');
    }

    /**
     * @Route("/{id}/edit", name="game_edit", methods={"GET", "POST"})
     */
    public function edit(
        Request $request,
        Game $game,
        GameRepository $gameRepository
    ): Response {
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
    public function view(
        Game $game,
        CompetitionRepository $competitionRepository,
        RegistrationRepository $registrationRepository
    ): Response {
        /** @var Player $player */
        $player = $this->getUser();
        return $this->render('game/show.html.twig', [
            'game' => $game,
            'competitions' => $competitionRepository->findByGame($game),
            'canEditGame' => $this->isGranted('ROLE_ADMIN'),
            'player'=> $player,
            'registrations' => $player ? $registrationRepository->findOpenByPlayer($player) : [],
        ]);
    }
}