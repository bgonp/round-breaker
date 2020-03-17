<?php

namespace App\DataFixtures;

use App\Entity\Competition;
use App\Entity\Round;
use App\Entity\Team;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RoundFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $competition = $manager->getRepository(Competition::class)->findAll()[0];
        $teams = $manager->getRepository(Team::class)->findAll();
        for ($i = 0; $i < 2; $i++) {
            $round = new Round();
            $round->setCompetition($competition);
            $round->addTeam($teams[$i*2]);
            $round->addTeam($teams[$i*2+1]);
            $round->setBestOf(3);
            $round->setBracketLevel(1);
            $round->setBracketOrder($i+1);
            $manager->persist($round);
        }
        $manager->flush();
    }

    public function getDependencies()
    {
        return [CompetitionFixtures::class, TeamFixtures::class];
    }
}
