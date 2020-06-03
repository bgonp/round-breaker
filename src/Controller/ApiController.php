<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Competition;
use App\Entity\Round;
use App\Entity\Team;
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
        Round $round,
        Team $team,
        CompetitionService $competitionService
    ): JsonResponse {
        if (
            !$this->isGranted('ROLE_USER') ||
            !$round->getCompetition()->getStreamer()->equals($this->getUser())
        ) {
            return new JsonResponse([], JsonResponse::HTTP_FORBIDDEN);
        }
        $affectedRound = null;
        $response = [
            'origin' => [
                'round_id' => $round->getId(),
                'teams' => [],
                'winner' => false,
            ]
        ];
        foreach ($round->getTeams() as $roundTeam) {
            $response['origin']['teams'][] = $roundTeam->getId();
            if ($roundTeam->equals($team)) {
                try {
                    if ($round->getWinner() && $round->getWinner()->equals($roundTeam)) {
                        $affectedRound = $competitionService->undoAdvanceTeam($roundTeam, $round);
                    } else {
                        $affectedRound = $competitionService->advanceTeam($roundTeam, $round);
                        $response['origin']['winner'] = $roundTeam->getId();
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
        if (
            !$this->isGranted('ROLE_USER') ||
            !$competition->getStreamer()->equals($this->getUser())
        ) {
            return new JsonResponse([], JsonResponse::HTTP_FORBIDDEN);
        }
        $twitchName = $request->request->get('twitch_name');
        $registration = $registrationRepository->findOneByCompetitionAndTwitchName($competition, $twitchName);
        if ($registration && $twitchName) {
            if (!$registration->getIsConfirmed()) {
                $registration->setIsConfirmed(true);
                $registrationRepository->save($registration);
            }
            return new JsonResponse($registration->getId(), JsonResponse::HTTP_OK);
        }
        return new JsonResponse([], JsonResponse::HTTP_BAD_REQUEST);
    }
}