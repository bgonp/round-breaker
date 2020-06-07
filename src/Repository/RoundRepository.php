<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\Round;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class RoundRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Round::class);
    }

    public function save(Round $round, bool $flush = true): void
    {
        $this->getEntityManager()->persist($round);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }

    public function refresh(Round $round): void
    {
        $this->getEntityManager()->refresh($round);
    }

    public function removeFromCompetition(Competition $competition, bool $flush = true): void
    {
        $this->createQueryBuilder('r')
            ->delete('App:Round', 'r')
            ->where('r.competition = :competition')
            ->setParameter('competition', $competition)
            ->getQuery()->execute();
    }
}
