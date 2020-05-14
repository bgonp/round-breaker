<?php

namespace App\Controller;

use App\Entity\Competition;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Service\TeamService;
use App\Service\PlayerService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Player;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TeamController extends AbstractController
{
    /**
     * @Route("/team/new", name="team_new")
     */
    public function createTeam(
        Request $request,
        TeamRepository $teamRepository,
        TeamService $teamService)
    {
        if ($request->request->has('name')) {
            $team = $teamRepository->findOneBy(['name' => $request->request->get('name')]);
            if (!$team) {
               $teamService->createTeam($request->request->get('name'));
            }
            return $this->redirectToRoute('main');
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            return $this->render('main/createTeam.html.twig', [
                'controller_name' => 'MainController',
                'teams' => $entityManager->getRepository(Player::Class)->findAll()
            ]);
        }
    }

    /**
     * @Route("/team/join", name="team_join")
     */
    public function joinTeam(
        Request $request,
        TeamRepository $teamRepository,
        PlayerRepository $playerRepository,
        PlayerService $playerService)
    {
        if ($request->request->has('team')) {
            $team = $teamRepository->findOneBy(['name' => $request->request->get('team')]);
            $user = $this->getUser()->getUsername();
            $user = $playerRepository->findOneBy(['username' => $user]);
            if ($team) {
                $playerService->addUserToTeam($team, $user);
            }
            return $this->redirectToRoute('main');
        } else {
            return $this->render('main/joinTeam.html.twig', [
                'controller_name' => 'MainController',
                'teams' => $teamRepository->findAll(),
                'players' => $playerRepository->findAll()
            ]);
        }
    }

    /**
     * @Route("/competition/{id}/randomize", name="competition_randomize")
     */
    public function randomizeTeams(
        Competition $competition,
        PlayerRepository $playerRepository,
        TeamService $teamService
    ) {
        $user = $this->getUser()->getUsername();
        $user = $playerRepository->findOneBy(['username' => $user]);
        $teamService->randomize($user, $competition);

        return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
    }
}