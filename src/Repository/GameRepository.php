<?php

namespace App\Repository;

use App\Entity\Game;
use App\Exception\CannotDeleteGameException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Exception\ForeignKeyConstraintViolationException;

class GameRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Game::class);
    }

    public function findAllOrdered(): array
    {
        return $this->findBy([], ['name' => 'ASC']);
    }

    public function findMostPlayed(): array
    {
        return $this->createQueryBuilder('g')
            ->leftJoin('g.competitions', 'c')
            ->select('g', 'COUNT(c.id)')
            ->groupBy('g')
            ->orderBy('COUNT(c.id)', 'DESC')
            ->setMaxResults(5)
            ->getQuery()->execute();
    }

    public function save(Game $game, bool $flush = true): void
    {
        $this->getEntityManager()->persist($game);
        if ($flush) {
            $this->flush();
        }
    }

    public function remove(Game $game, bool $flush = true): void
    {
        try {
            $this->getEntityManager()->remove($game);
            if ($flush) {
                $this->flush();
            }
        } catch (ForeignKeyConstraintViolationException $e) {
            throw CannotDeleteGameException::create();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
