<?php

namespace App\DataFixtures;

use App\Entity\Competition;
use App\Entity\Round;
use App\Entity\Team;
use App\Service\CompetitionService;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class RoundFixtures extends Fixture implements DependentFixtureInterface
{
    public function load(ObjectManager $manager)
    {
        $competition = $manager->getRepository(Competition::class)->findAll()[0];
        $teams = $manager->getRepository(Team::class)->findAll();
        $teams2; $teams3;
        for ($i = 0; $i < 4; $i++) {
            $round = new Round();
            $round->setCompetition($competition);
            $round->addTeam($teams[$i*2]);
            $round->addTeam($teams[$i*2+1]);
            $round->setBestOf(3);
            $round->setBracketLevel(1);
            $round->setBracketOrder($i+1);
            $round->setWinner($teams[$i*2]);
            $teams2[] = $teams[$i*2];
            $manager->persist($round);
        }

        for ($i = 0; $i < 2; $i++) {
            $round = new Round();
            $round->setCompetition($competition);
            $round->addTeam($teams2[$i*2]);
            $round->addTeam($teams2[$i*2+1]);
            $round->setBestOf(3);
            $round->setBracketLevel(2);
            $round->setBracketOrder($i+1);
            $round->setWinner($teams2[$i*2]);
            $teams3[] = $teams2[$i*2];
            $manager->persist($round);
        }

        $round = new Round();
        $round->setCompetition($competition);
        $round->addTeam($teams3[0]);
        $round->addTeam($teams3[1]);
        $round->setBestOf(3);
        $round->setBracketLevel(3);
        $round->setBracketOrder(1);
        $round->setWinner($teams3[0]);
        $manager->persist($round);

        $manager->flush();
    }

    public function getDependencies()
    {
        return [CompetitionFixtures::class, TeamFixtures::class];
    }
}
