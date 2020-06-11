<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Competition;
use App\Entity\Registration;
use App\Exception\RegistrationAlreadyExistsException;
use App\Repository\RegistrationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/registration")
 */
class RegistrationController extends BaseController
{
    /**
     * @Route("/new", name="registration_new", methods={"POST"})
     */
    public function new(
        Competition $competition,
        RegistrationRepository $registrationRepository
    ): Response {
        if (!($player = $this->getPlayer())) {
            $this->addFlash('error', 'Tienes que iniciar sesión para unirte a una competición');
        } elseif ($this->isGranted('ROLE_ADMIN')) {
            $this->addFlash('error', 'El administrador no puede unirse a competiciones');
        } elseif (!$competition->getIsOpen()) {
            $this->addFlash('error', 'Esta competición esta cerrada');
        } elseif (!$player->getTwitchName()) {
            $this->addFlash('error', 'Necesitas un nombre de usuario en Twitch para unirte');
        } else {
            $registration = (new Registration())
                ->setCompetition($competition)
                ->setPlayer($player);
            try {
                $registrationRepository->save($registration);
            } catch (RegistrationAlreadyExistsException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->redirectToRoute(
            $competition->getStreamer()->equals($player) || $this->isGranted('ROLE_ADMIN')
                ? 'competition_edit'
                : 'competition_show',
            ['id' => $competition->getId()]
        );
    }

    /**
     * @Route("/delete", name="registration_delete", methods={"POST"})
     */
    public function delete(Request $request, RegistrationRepository $registrationRepository): Response
    {
        if ($registrationId = $request->request->get('registration_id')) {
            $registration = $registrationRepository->find($registrationId);
        } elseif ($competitionId = $request->request->get('competition_id')) {
            $registration = $registrationRepository->findOneBy([
                'competition' => $competitionId,
                'player' => $this->getPlayer(),
            ]);
        }

        if (!$registration) {
            $this->addFlash('error', 'No se ha podido obtener la inscripción');

            return $this->redirectToRoute('competition_list', ['page' => 1]);
        } elseif (
            !$registration->getPlayer()->equals($this->getPlayer()) &&
            !$registration->getCompetition()->getStreamer()->equals($this->getPlayer()) &&
            !$this->isGranted('ROLE_ADMIN')
        ) {
            $this->addFlash('error', 'No puedes eliminar la inscripción de otro jugador');
        } elseif (!$registration->getCompetition()->getIsOpen()) {
            $this->addFlash('error', 'No puedes eliminar una inscripción de una competición cerrada');
        } else {
            $registrationRepository->remove($registration);

            return $this->redirectToRoute('competition_edit', ['id' => $registration->getCompetition()->getId()]);
        }

        return $this->redirectToRoute('competition_show', ['id' => $registration->getCompetition()->getId()]);
    }

    /** @Route("/toggle_confirmation", name="toggle_confirmation", methods={"POST"}) */
    public function toggleConfirmation(
        Request $request,
        Registration $registration,
        RegistrationRepository $registrationRepository
    ): Response {
        if (
            !$registration->getCompetition()->getStreamer()->equals($this->getPlayer()) &&
            !$this->isGranted('ROLE_ADMIN')
        ) {
            $this->addFlash('error', 'No puedes editar la inscripción de otro jugador');
        } elseif (!$registration->getCompetition()->getIsOpen()) {
            $this->addFlash('error', 'No puedes editar una inscripción si la competición esta cerrada');
        } else {
            $registration->setIsConfirmed((bool) $request->request->get('confirm'));
            $registrationRepository->save($registration);
        }

        return $this->redirectToRoute('competition_edit', ['id' => $registration->getCompetition()->getId()]);
    }
}
