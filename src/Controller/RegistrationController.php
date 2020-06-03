<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Registration;
use App\Repository\PlayerRepository;
use App\Repository\RegistrationRepository;
use App\Repository\TeamRepository;
use App\Service\CompetitionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
        RegistrationRepository $registrationRepository
    ) {
        $registrationRepository->remove($registration);
        return $this->redirectToRoute('competition_show', [
            'id' => $registration->getCompetition()->getId()
        ]);
    }
}