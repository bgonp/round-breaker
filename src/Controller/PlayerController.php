<?php

namespace App\Controller;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PlayerController extends AbstractController
{
    /**
     * @Route("/player/{id}", name="player_show", methods={"GET"})
     */
    public function view(PlayerRepository $playerRepository, Player $player): Response
    {
        return $this->render('player/show.html.twig', [
            'player' => $player,
        ]);
    }

    /**
     * @Route("/profile", name="profile", methods={"GET","POST"})
     */
    public function profile(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        PlayerRepository $playerRepository
    ): Response {
        if (!($player = $this->getUser())) {
            $this->addFlash('error', 'Inicia sesión para entrar en tu perfil');

            return $this->redirectToRoute('main');
        }
        if ($request->isMethod('POST')) {
            $this->editPlayer($player, $request, $passwordEncoder, $playerRepository);
        }

        return $this->render('player/edit.html.twig', ['player' => $player]);
    }

    /**
     * @Route("/player/{id}/edit", name="player_edit", methods={"GET", "POST"})
     */
    public function edit(
        Player $player,
        Request $request,
        PlayerRepository $playerRepository,
        UserPasswordEncoderInterface $passwordEncoder
    ): Response {
        if (!$this->isGranted('ROLE_ADMIN')) {
            if ($player->equals($this->getUser())) {
                return $this->redirectToRoute('profile');
            } else {
                $this->addFlash('error', 'No puedes editar información de otros jugadores');

                return $this->redirectToRoute('player_show', ['id' => $player->getId()]);
            }
        }
        if ($request->isMethod('POST')) {
            $this->editPlayer($player, $request, $passwordEncoder, $playerRepository);
        }

        return $this->render('player/edit.html.twig', [
            'player' => $player,
        ]);
    }

    private function editPlayer(
        Player $player,
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        PlayerRepository $playerRepository
    ): void {
        $player
            ->setUsername($request->request->get('username'))
            ->setEmail($request->request->get('email'))
            ->setTwitchName($request->request->get('twitch_name'));
        if ($plainPassword = $request->request->get('password')) {
            $player->setPassword($passwordEncoder->encodePassword($player, $plainPassword));
        }
        try {
            $playerRepository->save($player);
        } catch (UniqueConstraintViolationException $e) {
            $this->addFlash('error', 'Ya existe otro usuario con esos datos');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Ocurrió un error al actualizar el jugador');
        }
    }
}
