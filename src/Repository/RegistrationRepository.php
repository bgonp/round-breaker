<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Registration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Registration|null find($id, $lockMode = null, $lockVersion = null)
 * @method Registration|null findOneBy(array $criteria, array $orderBy = null)
 * @method Registration[]    findAll()
 * @method Registration[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RegistrationRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Registration::class);
    }

    public function save(Registration $registration, bool $flush = true)
    {
        $this->getEntityManager()->persist($registration);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findOneByCompetitionAndTwitchName(Competition $competition, string $twitchName): ?Registration
    {
        $registrations = $this->createQueryBuilder('r')
            ->join('r.player', 'p')
            ->where('r.competition = :competition')
            ->andWhere('p.twitch_name = :twitchname')
            ->setParameter('competition', $competition)
            ->setParameter('twitchname', $twitchName)
            ->getQuery()->getResult();

        if (count($registrations) === 0) {
            return null;
        }
        return $registrations[0];
    }

    public function findOneByPlayerAndCompetition(Player $player, Competition $competition): ?Registration
    {
        return $this->createQueryBuilder('r')
            ->where('r.competition = :competition')
            ->andWhere('r.player = :player')
            ->setParameter('competition', $competition)
            ->setParameter('player', $player)
            ->getQuery()->getOneOrNullResult();
    }

    /** @return Collection|Registration[] */
    public function findConfirmedByCompetitionRandomized(Competition $competition, int $maxResults): Collection
    {
        return $this->createQueryBuilder('r')
            ->where('r.competition = :competition')
            ->andWhere('r.isConfirmed = 1')
            ->setMaxResults($maxResults)
            ->setParameter('competition', $competition)
            ->orderBy('RAND()')
            ->getQuery()->execute();
    }

    /** @return Collection|Registration[] */
    public function findRandomConfirmedNotInTeam(Competition $competition): Collection
    {
        $qb = $this->createQueryBuilder('r');
        return $qb
            ->where('r.competition = :competition')
            ->andWhere('r.isConfirmed = 1')
            ->andWhere('r NOT IN (:registrations)')
            ->orderBy('RAND()')
            ->setParameter('competition', $competition)
            ->setParameter('registrations', $qb
                ->join('r.competition', 'c')
                ->join('c.teams', 't')
                ->join('t.players', 'p')
                ->where('r.competition = :competition')
                ->setParameter('competition', $competition)
                ->getDQL()
            )->getQuery()->execute();
    }

    public function remove(Registration $registration, bool $flush = true)
    {
        $this->getEntityManager()->remove($registration);
        if ($flush) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }

    // /**
    //  * @return Registration[] Returns an array of Registration objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('r.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Registration
    {
        return $this->createQueryBuilder('r')
            ->andWhere('r.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
