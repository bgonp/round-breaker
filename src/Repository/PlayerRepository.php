<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PlayerRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Player::class);
    }

    public function findOneConfirmedNotInTeamRandomized(Competition $competition)
    {
        return $this->createQueryBuilder('p')
            ->join('p.registrations', 'r')
            ->leftJoin('p.teams', 't')
            ->where('r.competition = :competition')
            ->andWhere('t.competition IS NULL')
            ->andWhere('r.isConfirmed = 1')
            ->orderBy('RAND()')
            ->setParameter('competition', $competition)
            ->setMaxResults(1)
            ->getQuery()->getOneOrNullResult();
    }

    public function findRandomized(int $count): array
    {
        return $this->createQueryBuilder('p')
            ->where('p.roles NOT LIKE :admin')
            ->setParameter('admin', '%ROLE_ADMIN%')
            ->orderBy('RAND()')
            ->setMaxResults($count)
            ->getQuery()->execute();
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        if (!$user instanceof Player) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newEncodedPassword);
        $this->save($user);
    }

    public function save(Player $player, bool $flush = true)
    {
        $this->getEntityManager()->persist($player);
        if ($flush) {
            $this->flush();
        }
    }

    public function flush()
    {
        $this->getEntityManager()->flush();
    }
}
