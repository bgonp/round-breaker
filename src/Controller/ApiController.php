<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\RoundRepository;
use App\Repository\TeamRepository;
use App\Service\CompetitionService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/api") */
class ApiController
{
    /** @Route("/set_round_winner", name="api_winner", methods={"PUT"}) */
    public function setRoundWinner(
        Request $request,
        CompetitionService $competitionService,
        RoundRepository $roundRepository,
        TeamRepository $teamRepository
    ): JsonResponse {
        $roundId = $request->get('round_id');
        $round = $roundRepository->find($roundId);
        $teamId = $request->get('team_id');
        $affectedRound = null;
        foreach ($round->getTeams() as $team) {
            if ($team->getId() === $teamId) {
                if ($round->getWinner()->equals($team)) {
                    $affectedRound = $competitionService->undoAdvanceTeam($team, $round);
                } else {
                    $affectedRound = $competitionService->advanceTeam($team, $round);
                }
                break;
            }
        }

        $data = [];
        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    /** @Route("/confirm_registration", name="api_confirm", methods={"PUT"}) */
    public function confirmRegistration(Request $request): JsonResponse
    {

    }
}