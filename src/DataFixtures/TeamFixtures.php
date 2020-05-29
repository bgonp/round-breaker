<?php

namespace App\DataFixtures;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class TeamFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $competition = $manager->getRepository(Competition::class)->findAll()[0];
        $players = $manager->getRepository(Player::class)->findAll();
        for ($i = 0; $i < 8; $i++) {
            $team = new Team();
            $team->setCompetition($competition);
            $team->setName('Nombre de prueba '.($i+1));
            $team->setCaptain($players[$i*3]);
            $team->addPlayer($players[$i*3]);
            $team->addPlayer($players[$i*3+1]);
            $team->addPlayer($players[$i*3+2]);
            $manager->persist($team);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [CompetitionFixtures::class, PlayerFixtures::class];
    }
}
