<?php

namespace App\DataFixtures;

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
        $competitions = $this->competitionRepository->findAllOrdered();
        foreach ($competitions as $index => $competition) {
            $registrationCount = $competition->getMaxPlayers() + (4 - ($index % 6)) * 5;
            if ($registrationCount < 0) {
                $registrationCount = $competition->getMaxPlayers();
            }
            $players = $this->playerRepository->findRandomized($registrationCount);
            for ($i = 0; $i < count($players); ++$i) {
                $this->registrationRepository->save((new Registration())
                    ->setCompetition($competition)
                    ->setPlayer($players[$i])
                    ->setIsConfirmed($i > 4),
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
