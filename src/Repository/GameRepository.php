<?php

namespace App\Repository;

use App\Entity\Game;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function findMostsPlayed(): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.competitions', 'c')
            ->select('g', 'COUNT(c.id)')
            ->groupBy('g')
            ->orderBy('COUNT(c.id)', 'DESC')
            ->setMaxResults(5)
            ->getQuery()->execute();
    }

    public function save(Game $game, bool $flush = true)
    {
        $this->getEntityManager()->persist($game);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
