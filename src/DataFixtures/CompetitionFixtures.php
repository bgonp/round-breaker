<?php

namespace App\DataFixtures;

use App\Entity\Competition;
use App\Entity\Game;
use App\Entity\Player;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class CompetitionFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $game = $manager->getRepository(Game::class)->findAll()[0];
        $competition = new Competition();
        $competition->setName('Competición de prueba #1');
        $competition->setGame($game);
        $competition->setStreamer($manager->getRepository(Player::class)->findOneBy(['username' => "Tester01"]));
        $competition->setHeldAt(new \DateTime());
        $competition->setIsOpen(false);
        $competition->setIsFinished(true);
        $competition->setIsIndividual(false);
        $competition->setPlayersPerTeam(3);
        $competition->setMaxPlayers(12);
        $manager->persist($competition);
        $manager->flush();
    }

    public function getDependencies()
    {
        return [GameFixtures::class];
    }
}
