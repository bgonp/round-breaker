<?php

namespace App\Controller;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class SecurityController extends AbstractController
{
    /**
     * @Route("/login", name="app_login")
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('main');
        }
        $error = $authenticationUtils->getLastAuthenticationError();
        if ($error) {
            $this->addFlash('error', $error->getMessage());
        } else {
            $this->addFlash('success', 'Logged in successfully');
        }
        $params = $error ? ['last_username' => $authenticationUtils->getLastUsername()] : [];
        return $this->redirectToRoute('main', $params);
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
        PlayerRepository $playerRepository
    ): Response {
        if (
            !($username = $request->get('username')) ||
            !($plainPassword = $request->get('password')) ||
            !($email = $request->get('email')) ||
            !($twitchname = $request->get('twitchname'))
        ) {
            throw new \InvalidArgumentException("Some required fields missing");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Wrong E-mail");
        }

        $player = new Player();
        $player
            ->setUsername($username)
            ->setTwitchName($twitchname)
            ->setEmail($email)
            ->setPassword($passwordEncoder->encodePassword($player, $plainPassword));

        $playerRepository->save($player);
        return $this->redirectToRoute('main');
    }
}
