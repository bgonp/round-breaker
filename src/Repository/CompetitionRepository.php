<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\Game;
use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class CompetitionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Competition::class);
    }

    public function save(Competition $competition, bool $flush = true): void
    {
        $this->getEntityManager()->persist($competition);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Competition $competition, bool $flush = true)
    {
        $this->getEntityManager()->remove($competition);
        if ($flush) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }

    public function countByGame(Game $game = null): int
    {
        if ($game) {
            return $this->count(['game' => $game]);
        }
        return $this->count([]);
    }

    /** @return Competition[] */
    public function findAllOrdered(int $page = null, int $perPage = 20): array
    {
        return $this->findByGameOrdered(null, $page, $perPage);
    }

    /** @return Competition[] */
    public function findByGameOrdered(Game $game = null, int $page = null, int $perPage = 20): array
    {
        $filter = $game ? ['game' => $game] : [];
        if (!$page) {
            return $this->findBy($filter, ['heldAt' => 'DESC']);
        } else {
            return $this->findBy($filter, ['heldAt' => 'DESC'], $perPage, ($page - 1) * $perPage);
        }
    }

    public function findLastByStreamer(Player $player): ?Competition
    {
        return $this->findOneBy(['streamer' => $player], ['updatedAt' => 'DESC']);
    }

    public function findOneRandomFinished(int $greaterThan = 1): ?Competition
    {
        return $this->createQueryBuilder('c')
            ->orderBy('RAND()')
            ->where('c.heldAt >= :since')
            ->andWhere('c.isFinished = 1')
            ->andWhere('c.maxPlayers > :greater_than')
            ->setParameter('since', strtotime('-3 month'))
            ->setParameter('greater_than', $greaterThan)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    public function findCompleteById(int $competitionId): ?Competition
    {
        return $this->createQueryBuilder('c')
            ->select('c', 'r', 't', 'p')
            ->leftJoin('c.rounds', 'r')
            ->leftJoin('r.teams', 't')
            ->leftJoin('t.players', 'p')
            ->where('c.id = :id')
            ->setParameter('id', $competitionId)
            ->orderBy('r.bracketLevel', 'ASC')
            ->addOrderBy('r.bracketOrder', 'ASC')
            ->addOrderBy('t.id')
            ->getQuery()->getOneOrNullResult();
    }

    /** @return Player[] */
    public function findByStreamer(Player $streamer): array
    {
        return $this->findBy(['streamer' => $streamer]);
    }

    /** @return Player[] */
    public function findByPlayer(Player $player): ?array
    {
        return $this->createQueryBuilder('c')
            ->join('c.registrations', 'r')
            ->where('r.player = :player')
            ->setParameter('player', $player)
            ->orderBy('c.heldAt', 'DESC')
            ->getQuery()->execute();
    }

    public function getTotalCount(): int
    {
        return $this->createQueryBuilder('c')
            ->select('COUNT(c.id)')
            ->getQuery()->getSingleScalarResult();
    }
}
