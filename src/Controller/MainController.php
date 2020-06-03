<?php

namespace App\Controller;

use App\Repository\CompetitionRepository;
use App\Repository\RegistrationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main", methods={"GET"})
     */
    public function main(Request $request, CompetitionRepository $competitionRepository): Response
    {
        $user = $this->getUser();
        $isAuthed = $user !== null;
        $randomCompetition = $competitionRepository->findRandomFinishedWithRoundsAndTeams();
        return $this->render('main/index.html.twig', [
            'last_username' => $request->get('last_username'),
            'competition' => $randomCompetition,
            'clickable' => false,
            'loggedUser' => $user,
            'createCompetitionButton' => $isAuthed,
            'createGameButton' => $isAuthed && $this->isGranted('ROLE_ADMIN')
        ]);
    }

    /**
     * @Route("/test", name="test")
     */
    public function test(CompetitionRepository $competitionRepository, RegistrationRepository $registrationRepository): void
    {
        $competition = $competitionRepository->find(1);
    }
}