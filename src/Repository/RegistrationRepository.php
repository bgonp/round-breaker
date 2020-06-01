<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\Registration;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    public function findByCompetitionAndTwitchName(Competition $competition, int $twitchName): ?Registration
    {
        $registrations = $this->createQueryBuilder('r')
            ->from('App:Registration', 'r')
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
