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
    public function edit(Request $request, Team $team, TeamRepository $teamRepository)
    {
        if ($team->getCaptain()->equals($this->getUser()) || $this->isGranted('ROLE_ADMIN')) {
            if ($request->isMethod('POST')) {
                $teamRepository->save($team->setName($request->request->get('name')));
            }
            return $this->render('team/edit.html.twig', ['team' => $team]);
        }
        return $this->redirectToRoute('competition_show', ['id' => $team->getCompetition()->getId()]);
    }

    /**
     * @Route("/{id}", name="team_show", methods={"GET"})
     */
    public function show(Team $team)
    {
        return $this->render('team/show.html.twig', ['team' => $team]);
    }
}