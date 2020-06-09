<?php

namespace App\Controller;

use App\Entity\Game;
use App\Exception\CannotDeleteGameException;
use App\Repository\CompetitionRepository;
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use App\Repository\RegistrationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/game")
 */
class GameController extends BaseController
{
    /**
     * @Route("/", name="game_list", methods={"GET"})
     */
    public function index(GameRepository $gameRepository, PlayerRepository $playerRepository): Response
    {
        return $this->render('game/index.html.twig', [
            'games' => $gameRepository->findAllOrdered(),
            'player' => $this->getPlayer(),
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
        } else {
            try {
                $gameRepository->remove($game);
            } catch (CannotDeleteGameException $e) {
                $this->addFlash('error', $e->getMessage());
            }
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
            if ($name = $request->request->get('name')) {
                if ($gameRepository->findOneBy(['name' => $name])) {
                    $this->addFlash('error', 'Ya existe un juego con el mismo nombre');
                } else {
                    $game->setName($name);
                    $game->setDescription($request->request->get('description'));
                    $gameRepository->save($game);
                }
            }

            return $this->render('game/edit.html.twig', ['game' => $game]);
        } else {
            $this->addFlash('error', 'Solo el administrador puede editar juegos');

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
        $perPage = 12;
        $lastPage = (int) ceil($competitionRepository->count(['game' => $game]) / $perPage);
        $currentPage = $page < 1 ? 1 : ($page > $lastPage ? $lastPage : $page);
        if ($currentPage !== $page) {
            return $this->redirectToRoute('game_show', ['id' => $game->getId(), 'page' => $currentPage]);
        }

        return $this->render('game/show.html.twig', [
            'game' => $game,
            'currentPage' => $currentPage,
            'perPage' => $perPage,
            'lastPage' => $lastPage,
            'competitions' => $competitionRepository->findByGameOrdered($game, $page, $perPage),
            'canEditGame' => $this->isGranted('ROLE_ADMIN'),
            'player' => $this->getPlayer(),
            'registrations' => $this->getPlayer() ? $registrationRepository->findOpenByPlayer($this->getPlayer()) : [],
        ]);
    }
}
