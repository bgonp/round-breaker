<?php

namespace App\Controller;

use App\Entity\Player;
use App\Exception\InvalidPlayerDataException;
use App\Repository\CompetitionRepository;
use App\Repository\PlayerRepository;
use App\Repository\RegistrationRepository;
use App\Repository\TeamRepository;
use App\Service\PlayerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PlayerController extends BaseController
{
    /**
     * @Route("/player/{id<\d+>}", name="player_show", methods={"GET"})
     */
    public function view(
        Player $player,
        CompetitionRepository $competitionRepository,
        RegistrationRepository $registrationRepository,
        TeamRepository $teamRepository)
    {
        return $this->render('player/show.html.twig', [
            'player' => $player,
            'user' => $this->getPlayer(),
            'competitions' => $competitionRepository->findByStreamer($player),
            'teams' => $teamRepository->findWithCompetitionAndGameByPlayer($player),
            'registrations' => $registrationRepository->findWithCompetitionAndGameByPlayer($player),
        ]);
    }

    /**
     * @Route("/profile", name="profile", methods={"GET","POST"})
     */
    public function profile(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        PlayerRepository $playerRepository,
        CompetitionRepository $competitionRepository,
        RegistrationRepository $registrationRepository,
        TeamRepository $teamRepository,
        PlayerService $playerService
    ): Response {
        if (!($player = $this->getPlayer())) {
            $this->addFlash('error', 'Inicia sesión para entrar en tu perfil');

            return $this->redirectToRoute('main');
        }
        if ($request->isMethod('POST')) {
            try {
                $playerService->editPlayer($player, $request, $passwordEncoder, $playerRepository, false);
            } catch (InvalidPlayerDataException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('player/edit.html.twig', [
            'player' => $player,
            'isAdmin' => $this->isGranted('ROLE_ADMIN'),
            'competitions' => $competitionRepository->findByStreamer($player),
            'teams' => $teamRepository->findWithCompetitionAndGameByPlayer($player),
            'registrations' => $registrationRepository->findWithCompetitionAndGameByPlayer($player),
        ]);
    }

    /**
     * @Route("/player/{id<\d+>}/edit", name="player_edit", methods={"GET", "POST"})
     */
    public function edit(
        Player $player,
        Request $request,
        PlayerRepository $playerRepository,
        UserPasswordEncoderInterface $passwordEncoder,
        PlayerService $playerService
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')) {
            if ($player->equals($this->getPlayer())) {
                return $this->redirectToRoute('profile');
            } else {
                $this->addFlash('error', 'No puedes editar información de otros jugadores');

                return $this->redirectToRoute('player_show', ['id' => $player->getId()]);
            }
        }

        if ($request->isMethod('POST')) {
            try {
                $playerService->editPlayer($player, $request, $passwordEncoder, $playerRepository, false);
            } catch (InvalidPlayerDataException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('player/edit.html.twig', [
            'player' => $player,
            'isAdmin' => in_array('ROLE_ADMIN', $player->getRoles()),
        ]);
    }
}
