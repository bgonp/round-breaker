<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Registration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

/**
 * @method Competition|null find($id, $lockMode = null, $lockVersion = null)
 * @method Competition|null findOneBy(array $criteria, array $orderBy = null)
 * @method Competition[]    findAll()
 * @method Competition[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
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

    /** @return Competition|null Random competition from the last 3 months or null if it doesn't exists */
    public function findRandomFinishedWithRoundsAndTeams(): ?Competition
    {
        $result = $this->createQueryBuilder('c')
            ->select('c', 'r', 't')
            ->join('c.rounds', 'r')
            ->join('r.teams', 't')
            ->orderBy('r.bracketLevel')
            ->addOrderBy('r.bracketOrder')
            ->addOrderBy('t.id')
            ->addOrderBy('RAND()')
            ->where('c.heldAt >= :since')
            ->andWhere('c.isFinished = 1')
            ->setParameter('since', strtotime('-3 month'))
            ->getQuery()->execute();
        return count($result) === 0 ? null : $result[0];
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

    /** @return Registration[]|Collection */
    public function findOpenByPlayerRegistered(Player $player): array
    {
        return $this->createQueryBuilder('c')
            ->join('c.registrations', 'r')
            ->where('c.isOpen = 1')
            ->andWhere('r.player = :player')
            ->setParameter('player', $player)
            ->getQuery()->execute();
    }
}
