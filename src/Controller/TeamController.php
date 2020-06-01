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

/**
 * @Route("/team")
 */
class TeamController extends AbstractController
{
    /**
     * @Route("/{id}/edit", name="team_edit", methods={"GET", "POST"})
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
            return $this->render('team/edit.html.twig', [
                'controller_name' => 'TeamController',
                'team' => $team
            ]);
        } else {
            return $this->redirectToRoute('main');
        }
    }

    /**
     * @Route("/{id}", name="team_show", methods={"GET"})
     */
    public function viewGame(
        Team $team
    ) {
        return $this->render('team/show.html.twig', [
            'controller_name' => 'TeamController',
            'team' => $team
        ]);
    }
}