<?php

namespace App\Repository;

use App\Entity\Round;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
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

    public function removeRounds(Collection $rounds, bool $flush = true): void
    {
        $roundsNum = count($rounds);
        for ($i = 0; $i < $roundsNum; ++$i) {
            $this->getEntityManager()->remove($rounds[$i]);
        }
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
