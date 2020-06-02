<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Competition;
use App\Repository\RegistrationRepository;
use App\Repository\RoundRepository;
use App\Service\CompetitionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/api") */
class ApiController extends AbstractController
{
    /** @Route("/set_round_winner", name="api_winner", methods={"PUT"}) */
    public function setRoundWinner(
        Request $request,
        CompetitionService $competitionService,
        RoundRepository $roundRepository
    ): JsonResponse {
        $roundId = $request->get('round_id');
        $round = $roundRepository->find($roundId);
        $teamId = $request->get('team_id');
        $affectedRound = null;
        $response = [
            'origin' => [
                'round_id' => $round->getId(),
                'teams' => [],
                'winner' => false,
            ]
        ];
        foreach ($round->getTeams() as $team) {
            $response['origin']['teams'][] = $team->getId();
            if ($team->getId() == $teamId) {
                try {
                    if ($round->getWinner() && $round->getWinner()->equals($team)) {
                        $affectedRound = $competitionService->undoAdvanceTeam($team, $round);
                    } else {
                        $affectedRound = $competitionService->advanceTeam($team, $round);
                        $response['origin']['winner'] = $teamId;
                    }
                } catch (\InvalidArgumentException $exception) {
                    return new JsonResponse([], JsonResponse::HTTP_BAD_REQUEST);
                }
                if ($affectedRound) {
                    $response['destination'] = [
                        'round_id' => $affectedRound->getId(),
                        'teams' => [],
                        'winner' => false,
                    ];
                    foreach ($affectedRound->getTeams() as $affectedTeam) {
                        $response['destination']['teams'][] = $affectedTeam->getId();
                    }
                }
            }
        }

        return new JsonResponse($response, JsonResponse::HTTP_OK);
    }

    /** @Route("/confirm_registration", name="api_confirm", methods={"PUT"}) */
    public function confirmRegistration(
        Request $request,
        Competition $competition,
        RegistrationRepository $registrationRepository
    ): JsonResponse {
        $isStreamer = $this->getUser() && $competition->getStreamer()->equals($this->getUser());
        $twitchName = $request->get('twitch_name');
        $registration = $registrationRepository->findByCompetitionAndTwitchName($competition, $twitchName);
        if ($isStreamer && $registration && $twitchName) {
            if (!$registration->getIsConfirmed()) {
                $registration->setIsConfirmed(true);
                $registrationRepository->save($registration);
            }
            return new JsonResponse($registration->getId(), JsonResponse::HTTP_OK);
        }
        return new JsonResponse([], JsonResponse::HTTP_BAD_REQUEST);
    }
}