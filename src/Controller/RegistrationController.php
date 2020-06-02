<?php

declare(strict_types=1);

namespace App\Controller;

use App\Repository\CompetitionRepository;
use App\Repository\PlayerRepository;
use App\Repository\RegistrationRepository;
use App\Service\CompetitionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/registration")
 */
class RegistrationController extends AbstractController
{
    /**
     * @Route("/new", name="registration_new", methods={"POST"})
     */
    public function makeRegistration(
        Request $request,
        PlayerRepository $playerRepository,
        CompetitionRepository $competitionRepository,
        CompetitionService $competitionService
    ) {
        if ($request->request->has('id')) {
            $user = $this->getUser();
            $isAuthed = $user !== null;
            $player = $isAuthed ? $playerRepository->findOneBy(["username" => $user->getUsername()]) : null;
            $competition = $competitionRepository->findOneBy(['id' => $request->request->get('id')]);
            if ($competition && $competition->getIsOpen()) {
                $competitionService->addPlayerToCompetition($competition, $player);
            }
            return $this->redirectToRoute('competition_show', array('id' => $request->request->get('id')));
        } else {
            return $this->redirectToRoute('main');
        }
    }

    /**
     * @Route("/delete", name="registration_delete", methods={"POST"})
     */
    public function deleteRegistration(
        Request $request,
        PlayerRepository $playerRepository,
        CompetitionRepository $competitionRepository,
        RegistrationRepository $registrationRepository
    ) {
        if ($request->request->has('competitionId')) {
            $player = $playerRepository->findOneBy(['id' => $request->request->get('playerId')]);
            $competition = $competitionRepository->findOneBy(['id' => $request->request->get('competitionId')]);
            $registration = $registrationRepository->findOneBy([
                'player' => $player,
                'competition' => $competition
            ]);
            if (
                $competition && $competition->getIsOpen() && $registration &&
                ($this->isGranted("ROLE_ADMIN") ||
                $player->getUsername() == $this->getUser()->getUsername())
            ) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->remove($registration);
                $entityManager->flush();
            }
            return $this->redirectToRoute('competition_list');
        } else {
            return $this->redirectToRoute('main');
        }
    }
}