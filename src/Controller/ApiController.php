<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Competition;
use App\Entity\Round;
use App\Entity\Team;
use App\Exception\CannotModifyWinnerException;
use App\Repository\CompetitionRepository;
use App\Repository\RegistrationRepository;
use App\Service\RoundService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/** @Route("/api") */
class ApiController extends AbstractController
{
    /** @Route("/set_round_winner", name="api_winner", methods={"POST"}) */
    public function setRoundWinner(
        Round $round,
        Team $team,
        RoundService $competitionService,
        CompetitionRepository $competitionRepository
    ): JsonResponse {
        if (
            !$this->isGranted('ROLE_ADMIN') &&
            (!$this->isGranted('ROLE_USER') ||
            !$round->getCompetition()->getStreamer()->equals($this->getUser()))
        ) {
            return new JsonResponse(
                ['message' => 'No puedes asignar ganador de una competción que no es tuya'],
                JsonResponse::HTTP_FORBIDDEN
            );
        } elseif ($round->getTeams()->count() < 2) {
            return new JsonResponse(
                ['message' => 'No puede ganar si solo hay un equipo en la ronda'],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }

        $response = [
            'finished' => false,
            'origin' => [
                'round_id' => $round->getId(),
                'teams' => [],
                'winner' => false,
            ],
        ];
        foreach ($round->getTeams() as $roundTeam) {
            $response['origin']['teams'][] = $roundTeam->getId();
            if ($roundTeam->equals($team)) {
                $competition = $round->getCompetition();
                $affectedRound = null;

                try {
                    if ($round->getWinner() && $round->getWinner()->equals($roundTeam)) {
                        $affectedRound = $competitionService->undoAdvanceTeam($roundTeam, $round);
                        if ($competition->getIsFinished()) {
                            $competitionRepository->save($competition->setIsFinished(false));
                        }
                    } else {
                        $affectedRound = $competitionService->advanceTeam($roundTeam, $round);
                        if (!$affectedRound && !$competition->getIsFinished()) {
                            $competitionRepository->save($competition->setIsFinished(true));
                            $response['finished'] = true;
                        }
                        $response['origin']['winner'] = $roundTeam->getId();
                    }
                } catch (CannotModifyWinnerException $e) {
                    return new JsonResponse(['message' => $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
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

    /** @Route("/confirm_registration", name="api_confirm", methods={"POST"}) */
    public function confirmRegistration(
        Request $request,
        Competition $competition,
        RegistrationRepository $registrationRepository
    ): JsonResponse {
        if (
            !$this->isGranted('ROLE_ADMIN') &&
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

            return new JsonResponse(['registration_id' => $registration->getId()], JsonResponse::HTTP_OK);
        }

        return new JsonResponse([], JsonResponse::HTTP_BAD_REQUEST);
    }

    /**
     * @Route("/open_confirmations", name="open_confirmations", methods={"POST"})
     */
    public function openConfirmations(
        Request $request,
        Competition $competition,
        CompetitionRepository $competitionRepository
    ): JsonResponse {
        if (
            !$this->isGranted('ROLE_ADMIN') &&
            !$competition->getStreamer()->equals($this->getUser())
        ) {
            return new JsonResponse([], JsonResponse::HTTP_FORBIDDEN);
        }

        $competition->setTwitchBotName($request->request->get('twitch_bot_name'));
        $competition->setTwitchBotToken($request->request->get('twitch_bot_token'));
        $competition->setTwitchChannel($request->request->get('twitch_channel'));
        $competitionRepository->save($competition);

        return new JsonResponse([], $competition->getIsOpen()
            ? JsonResponse::HTTP_OK
            : JsonResponse::HTTP_BAD_REQUEST
        );
    }
}
