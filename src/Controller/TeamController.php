<?php

namespace App\Controller;

use App\Entity\Team;
use App\Repository\TeamRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/team")
 */
class TeamController extends BaseController
{
    /**
     * @Route("/{id<\d+>}", name="team_show", methods={"GET", "POST"})
     */
    public function show(Request $request, Team $team, TeamRepository $teamRepository): Response
    {
        if (
            !$this->isGranted('ROLE_ADMIN') && (
            !($player = $this->getPlayer()) ||
            !$team->getPlayers()->contains($player))
        ) {
            $this->addFlash('error', 'Solo puedes ver los equipos a los que perteneces');

            return $this->redirectToRoute('competition_show', ['id' => $team->getCompetition()->getId()]);
        }
        $canEdit = $this->isGranted('ROLE_ADMIN') ||
            ($team->getCaptain()->equals($player) && !$team->getCompetition()->getIsFinished());
        if ($request->isMethod('POST')) {
            if ($canEdit) {
                $teamRepository->save($team->setName($request->request->get('name')));
            } else {
                $this->addFlash(
                    'error',
                    'No tienes permisos para editar el nombre del equipo o la competición ya ha finalizado'
                );
            }
        }

        return $this->render('team/show.html.twig', [
            'team' => $team,
            'canEditName' => $canEdit,
        ]);
    }
}
