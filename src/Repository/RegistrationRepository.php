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
    public function findConfirmedByCompetitionRandomized(Competition $competition, int $maxResults): array
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
    public function findConfirmedNotInTeamRandomized(Competition $competition): array
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

    /** @return Registration[]|Collection */
    public function findOpenByPlayer(Player $player): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.competition', 'c')
            ->where('c.isOpen = 1')
            ->andWhere('r.player = :player')
            ->setParameter('player', $player)
            ->getQuery()->execute();
    }

    public function save(Registration $registration, bool $flush = true): void
    {
        $this->getEntityManager()->persist($registration);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Registration $registration, bool $flush = true): void
    {
        $this->getEntityManager()->remove($registration);
        if ($flush) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
