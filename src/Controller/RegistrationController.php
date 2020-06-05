<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Competition;
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
        RegistrationRepository $registrationRepository
    ) {
        if (!($player = $this->getUser())) {
            $this->addFlash('error', 'Tienes que iniciar sesión para unirte a una competición');
        } else if (!$competition->getIsOpen()) {
            $this->addFlash('error', 'Esta competición esta cerrada');
        } else {
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
        if (
            !$registration->getPlayer()->equals($this->getUser()) &&
            !$registration->getCompetition()->getStreamer()->equals($this->getUser()) &&
            !$this->isGranted('ROLE_ADMIN')
        ) {
            $this->addFlash('error', 'No puedes eliminar la inscripción de otro jugador');
        } else if (!$registration->getCompetition()->getIsOpen()) {
            $this->addFlash('error', 'No puedes eliminar una inscripción de una competición cerrada');
        } else {
            if ($team = $teamRepository->findOneByPlayerAndCompetition($registration->getPlayer(), $registration->getCompetition())) {
                $team->removePlayer($registration->getPlayer());
                $teamRepository->save($team);
            }
            $registrationRepository->remove($registration);
        }
        return $this->redirectToRoute(
            $this->isGranted('ROLE_ADMIN') ? 'competition_edit' : 'competition_show', [
            'id' => $registration->getCompetition()->getId()
        ]);
    }

    /** @Route("/toggle_confirmation", name="toggle_confirmation", methods={"POST"}) */
    public function toggleConfirmation(
        Request $request,
        Registration $registration,
        RegistrationRepository $registrationRepository
    ): Response {
        if (
            !$registration->getPlayer()->equals($this->getUser()) &&
            !$registration->getCompetition()->getStreamer()->equals($this->getUser()) &&
            !$this->isGranted('ROLE_ADMIN')
        ) {
            $this->addFlash('error', 'No puedes editar la inscripción de otro jugador');
        } else if (!$registration->getCompetition()->getIsOpen()) {
            $this->addFlash('error', 'No puedes editar una inscripción si la competición esta cerrada');
        } else {
            $registration->setIsConfirmed($request->request->get('confirm')=="1");
            $registrationRepository->save($registration);
        }
        return $this->redirectToRoute('competition_edit', ['id' => $registration->getCompetition()->getId()]);
    }
}