<?php

namespace App\Repository;

use App\Entity\Competition;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
            $this->getEntityManager()->flush();
        }
    }

    /** @return Competition|null Random competition from the last 3 months or null if it doesn't exists */
    public function findRandomFinishedWithRoundsAndTeams(): ?Competition
    {
        $result = $this->createQueryBuilder('c')
            ->select('c', 'r', 't')
            ->join('c.rounds', 'r')
            ->join('r.teams', 't')
            ->orderBy('c.heldAt', 'DESC')
            ->where('c.heldAt >= :since')
            ->andWhere('c.isFinished = 1')
            ->setParameter('since', strtotime('-3 month'))
            ->getQuery()->execute();
        if (count($result) === 0) {
            return null;
        }
        return $result[rand(0, count($result)-1)];
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
            ->getQuery()->getOneOrNullResult();
    }

    // /**
    //  * @return Competition[] Returns an array of Competition objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('c.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Competition
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
