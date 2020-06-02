<?php
namespace App\Controller;

use App\Form\UserType;
use App\Entity\Player;
use App\Repository\PlayerRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegistrationController extends AbstractController
{
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

        /*// TODO: No debe tener vista aquí
        // 1) build the form
        $user = new Player();
        $form = $this->createForm(UserType::class, $user);

        // 2) handle the submit (will only happen on POST)
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            // 3) Encode the password (you could also do this via Doctrine listener)
            $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($password);

            // 4) save the User!
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            // ... do any other work - like sending them an email, etc
            // maybe set a "flash" success message for the user

            return $this->redirectToRoute('main');
        }

        return $this->render(
            'registration/register.html.twig',
            array('form' => $form->createView())
        );*/
    }
}