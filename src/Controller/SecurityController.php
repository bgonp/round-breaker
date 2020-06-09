<?php

namespace App\Controller;

use App\Entity\Player;
use App\Exception\InvalidPlayerDataException;
use App\Repository\PlayerRepository;
use App\Service\PlayerService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends BaseController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getPlayer()) {
            return $this->redirectToRoute('main');
        }

        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash('error', 'Credenciales incorrectas');
        }

        return $this->redirectToRoute('main');
    }

    /**
     * @Route("/logout", name="app_logout")
     */
    public function logout()
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
    }

    /**
     * @Route("/register", name="user_registration", methods={"POST"})
     */
    public function register(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        PlayerRepository $playerRepository,
        PlayerService $playerService
    ): Response {
        if ($this->getPlayer()) {
            return $this->redirectToRoute('main');
        }

        $player = new Player();
        try {
            $playerService->editPlayer($player, $request, $passwordEncoder, $playerRepository);
        } catch (InvalidPlayerDataException $e) {
            $this->addFlash('error', $e->getMessage());

            return $this->redirectToRoute('main', [
                'last_username' => $request->request->get('username'),
                'last_email' => $request->request->get('email'),
                'last_twitchname' => $request->request->get('twitchname'),
            ]);
        }
        $this->addFlash('success', '¡Felicidades! Ya eres parte de la comunidad Round Breaker, ahora puedes iniciar sesión y empezar a participar en los torneos.');

        return $this->redirectToRoute('main');
    }
}
