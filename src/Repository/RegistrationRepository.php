<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Registration;
use App\Exception\RegistrationAlreadyExistsException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

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
            ->andWhere('p.twitchName = :twitchname')
            ->setParameter('competition', $competition)
            ->setParameter('twitchname', $twitchName)
            ->getQuery()->getResult();

        if (0 === count($registrations)) {
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

    /** @return Registration[]|Collection */
    public function findWithCompetitionByPlayer(Player $player): array
    {
        return $this->createQueryBuilder('r')
            ->join('r.competition', 'c')
            ->where('r.player = :player')
            ->setParameter('player', $player)
            ->getQuery()->execute();
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
            try {
                $this->getEntityManager()->flush();
            } catch (UniqueConstraintViolationException $e) {
                throw RegistrationAlreadyExistsException::create();
            }
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
