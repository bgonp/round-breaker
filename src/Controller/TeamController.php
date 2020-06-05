<?php

namespace App\Controller;

use App\Entity\Competition;
use App\Entity\Player;
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
     * @Route("/{id}", name="team_show", methods={"GET", "POST"})
     */
    public function show(Request $request, Team $team, TeamRepository $teamRepository): Response
    {
        if (!($player = $this->getUser()) || $team->getPlayers()->contains($player)) {
            return $this->redirectToRoute('competition_show', ['id' => $team->getCompetition()->getId()]);
        }
        $canEditName = $team->getCaptain()->equals($player) || $this->isGranted('ROLE_ADMIN');
        if ($canEditName) {
            if ($request->isMethod('POST')) {
                $teamRepository->save($team->setName($request->request->get('name')));
            }
        }
        return $this->render('team/show.html.twig', [
            'team' => $team,
            'canEditName' => $canEditName
        ]);
    }
}