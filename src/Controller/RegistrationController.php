<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Registration;
use App\Repository\RegistrationRepository;
use App\Repository\TeamRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/registration")
 */
class RegistrationController extends AbstractController
{
    /**
     * @Route("/new", name="registration_new", methods={"POST"})
     */
    public function new(
        Competition $competition,
        Player $player,
        RegistrationRepository $registrationRepository
    ) {
        if ($competition->getIsOpen()) {
            $registration = (new Registration())
                ->setCompetition($competition)
                ->setPlayer($player);
            $registrationRepository->save($registration);
        }
        return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
    }

    /**
     * @Route("/delete", name="registration_delete", methods={"POST"})
     */
    public function delete(
        Registration $registration,
        RegistrationRepository $registrationRepository,
        TeamRepository $teamRepository
    ) {
        if ($team = $teamRepository->findOneByPlayerAndCompetition($registration->getPlayer(), $registration->getCompetition())) {
            $team->removePlayer($registration->getPlayer());
            $teamRepository->save($team);
        }
        $registrationRepository->remove($registration);
        return $this->redirectToRoute('competition_show', [
            'id' => $registration->getCompetition()->getId()
        ]);
    }

    /** @Route("/toggle_confirmation", name="toggle_confirmation", methods={"POST"}) */
    public function toggleConfirmation(
        Request $request,
        Registration $registration,
        RegistrationRepository $registrationRepository
    ): Response {
        $registration->setIsConfirmed($request->request->get('confirm')=="1");
        $registrationRepository->save($registration);
        return $this->redirectToRoute('competition_show', ['id' => $registration->getCompetition()->getId()]);
    }
}