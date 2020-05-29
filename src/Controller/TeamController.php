<?php

namespace App\Controller;

use App\Entity\Competition;
use App\Entity\Team;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Service\TeamService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
    /**
     * @Route("/competition/{id}/randomize", name="competition_randomize")
     */
    public function randomizeTeams(
        Competition $competition,
        PlayerRepository $playerRepository,
        TeamService $teamService
    ): Response {
        $user = $this->getUser()->getUsername();
        $user = $playerRepository->findOneBy(['username' => $user]);
        $teamService->randomize($user, $competition);

        return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
    }

    /**
     * @Route("/team/{id}/edit", name="team_edit", methods={"GET", "POST"})
     */
    public function editGame(
        Request $request,
        Team $team,
        TeamRepository $teamRepository
    ) {
        if ($team->getCaptain()->getUsername() == $this->getUser()->getUsername()) {
            if ($request->request->has('name')) {
                $team->setName($request->request->get('name'));
                $teamRepository->save($team);
            }
            return $this->render('main/editTeam.html.twig', [
                'controller_name' => 'TeamController',
                'team' => $team
            ]);
        } else {
            return $this->redirectToRoute('main');
        }
    }

    /**
     * @Route("/team/{id}", name="team_show", methods={"GET"})
     */
    public function viewGame(
        Team $team
    ) {
        return $this->render('main/viewTeam.html.twig', [
            'controller_name' => 'TeamController',
            'team' => $team
        ]);
    }
}