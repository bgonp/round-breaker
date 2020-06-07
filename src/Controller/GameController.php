<?php

namespace App\Controller;

use App\Entity\Game;
use App\Entity\Player;
use App\Repository\CompetitionRepository;
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use App\Repository\RegistrationRepository;
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
        return $this->render('game/index.html.twig', [
            'games' => $gameRepository->findAllOrdered(),
            'player' => $this->getUser(),
        ]);
    }

    /**
     * @Route("/new", name="game_new", methods={"GET", "POST"})
     */
    public function new(Request $request, GameRepository $gameRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'No puedes crear un juego');

            return $this->redirectToRoute('game_list');
        }
        $game = null;
        if ($request->isMethod('POST')) {
            $game = (new Game())
                ->setName($request->request->get('name'))
                ->setDescription($request->request->get('description'));
            if ($gameRepository->findOneBy(['name' => $request->request->get('name')])) {
                $this->addFlash('error', 'Ya existe un juego con el mismo nombre');
            } else {
                $gameRepository->save($game);

                return $this->redirectToRoute('game_list');
            }
        }

        return $this->render('game/new.html.twig', ['game' => $game]);
    }

    /**
     * @Route("/delete", name="game_delete", methods={"POST"})
     */
    public function delete(Game $game, GameRepository $gameRepository): Response
    {
        if (!$this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'No puedes eliminar juegos');

            return $this->redirectToRoute('game_list');
        }
        try {
            $gameRepository->remove($game);
        } catch (Exception $e) {
            $this->addFlash('error', 'No puedes borrar este juego');

            return $this->redirectToRoute('game_edit', ['id' => $game->getId()]);
        }

        return $this->redirectToRoute('game_list');
    }

    /**
     * @Route("/{id<\d+>}/edit", name="game_edit", methods={"GET", "POST"})
     */
    public function edit(
        Request $request,
        Game $game,
        GameRepository $gameRepository
    ): Response {
        if ($this->isGranted('ROLE_ADMIN')) {
            if ($request->request->has('name')) {
                $game->setName($request->request->get('name'));
                $game->setDescription($request->request->get('description'));
                $gameRepository->save($game);
            }

            return $this->render('game/edit.html.twig', [
                'controller_name' => 'GameController',
                'game' => $game,
            ]);
        } else {
            return $this->redirectToRoute('main');
        }
    }

    /**
     * @Route("/{id<\d+>}/page/{page<\d+>}", name="game_show", methods={"GET"})
     */
    public function view(
        Game $game,
        CompetitionRepository $competitionRepository,
        RegistrationRepository $registrationRepository,
        int $page = 0
    ): Response {
        $perPage = 20;
        $lastPage = (int) ceil($competitionRepository->count(['game' => $game]) / $perPage);
        $currentPage = $page < 1 ? 1 : ($page > $lastPage ? $lastPage : $page);
        if ($currentPage !== $page) {
            return $this->redirectToRoute('game_show', ['id' => $game->getId(), 'page' => $currentPage]);
        }

        /** @var Player $player */
        $player = $this->getUser();

        return $this->render('game/show.html.twig', [
            'game' => $game,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'lastPage' => $lastPage,
            'competitions' => $competitionRepository->findByGameOrdered($game, $page, $perPage),
            'canEditGame' => $this->isGranted('ROLE_ADMIN'),
            'player' => $player,
            'registrations' => $player ? $registrationRepository->findOpenByPlayer($player) : [],
        ]);
    }
}
