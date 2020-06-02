<?php

namespace App\Controller;

use App\Repository\CompetitionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main", methods={"GET"})
     */
    public function main(
        CompetitionRepository $competitionRepository,
        AuthenticationUtils $authenticationUtils
    ): Response {
        $user = $this->getUser();
        $isAuthed = $user !== null;
        $error = $authenticationUtils->getLastAuthenticationError();
        $lastUsername = $authenticationUtils->getLastUsername();
        $randomCompetition = $competitionRepository->findRandomFinishedWithRoundsAndTeams();
        return $this->render('main/index.html.twig', [
            'last_username' => $lastUsername,
            'error' => $error,
            'competition' => $randomCompetition,
            'clicable' => false,
            'loggedUser' => $user,
            'createCompetitionButton' => $isAuthed,
            'createGameButton' => $isAuthed && $this->isGranted('ROLE_ADMIN')
        ]);
    }
}