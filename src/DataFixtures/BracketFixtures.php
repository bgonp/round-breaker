<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\Competition;
use App\Exception\NotEnoughConfirmedRegistrationsException;
use App\Repository\CompetitionRepository;
use App\Service\CompetitionService;
use App\Service\RoundService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class BracketFixtures extends Fixture implements DependentFixtureInterface
{
    private CompetitionRepository $competitionRepository;

    private CompetitionService $competitionService;

    private RoundService $roundService;

    public function __construct(
        CompetitionRepository $competitionRepository,
        CompetitionService $competitionService,
        RoundService $roundService
    ) {
        $this->competitionRepository = $competitionRepository;
        $this->competitionService = $competitionService;
        $this->roundService = $roundService;
    }

    public function load(ObjectManager $manager)
    {
        $competitions = $this->competitionRepository->findAllOrdered();
        $finished = false;
        $now = new \DateTime();
        foreach ($competitions as $competition) {
            if ($now > $competition->getHeldAt()) {
                try {
                    $this->competitionService->randomize($competition);
                    if ($finished) {
                        $this->finishCompetition($competition);
                    } else {
                        $finished = true;
                    }
                    $this->competitionRepository->save($competition, false);
                } catch (NotEnoughConfirmedRegistrationsException $e) {
                }
            }
        }
        $this->competitionRepository->flush();
    }

    private function finishCompetition(Competition $competition)
    {
        $rounds = $competition->getRounds();
        foreach ($rounds as $round) {
            $winnerIndex = rand(0, $round->getTeams()->count() - 1);
            $this->roundService->advanceTeam($round->getTeams()->get($winnerIndex), $round);
        }
        $competition->setIsFinished(true);
    }

    public function getDependencies()
    {
        return [
            CompetitionFixtures::class,
            RegistrationFixtures::class,
        ];
    }
}
