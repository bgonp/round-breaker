<?php

namespace App\DataFixtures;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Registration;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RegistrationFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $players = $manager->getRepository(Player::class)->findAll();
        $competition = $manager->getRepository(Competition::class)->findAll()[0];
        foreach ($players as $player) {
            $registration = new Registration();
            $registration->setPlayer($player);
            $registration->setCompetition($competition);
            $manager->persist($registration);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [PlayerFixtures::class, CompetitionFixtures::class];
    }
}
