<?php

namespace App\DataFixtures;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Registration;
use App\Repository\CompetitionRepository;
use App\Repository\PlayerRepository;
use App\Repository\RegistrationRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RegistrationFixtures extends Fixture implements DependentFixtureInterface
{
    private RegistrationRepository $registrationRepository;

    private CompetitionRepository $competitionRepository;

    private PlayerRepository $playerRepository;

    public function __construct(
        RegistrationRepository $registrationRepository,
        CompetitionRepository $competitionRepository,
        PlayerRepository $playerRepository
    ) {
        $this->competitionRepository = $competitionRepository;
        $this->playerRepository = $playerRepository;
        $this->registrationRepository = $registrationRepository;
    }

    public function load(ObjectManager $manager)
    {
        $competitions = $this->competitionRepository->findAll();
        foreach ($competitions as $competition) {
            $registrationCount = rand(30, 60);
            $players = $this->playerRepository->findRandomized($registrationCount);
            for ($i = 0; $i < $registrationCount; $i++) {
                $this->registrationRepository->save((new Registration())
                    ->setCompetition($competition)
                    ->setPlayer($players[$i])
                    ->setIsConfirmed(!!rand(0,10)),
                false);
            }
        }
        $this->registrationRepository->flush();
    }

    public function getDependencies()
    {
        return [PlayerFixtures::class, CompetitionFixtures::class];
    }
}
