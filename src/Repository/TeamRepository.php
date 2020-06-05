<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Persistence\ManagerRegistry;

class TeamRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    public function findOneByPlayerAndCompetition(Player $player, Competition $competition): ?Team
    {
        return $this->createQueryBuilder('t')
            ->join('t.players', 'p')
            ->where('t.competition = :competition')
            ->andWhere('p = :player')
            ->setParameter('competition', $competition)
            ->setParameter('player', $player)
            ->getQuery()->getOneOrNullResult();
    }

	public function save(Team $team, bool $flush = true): void
	{
		$this->getEntityManager()->persist($team);
		if ($flush) {
			$this->getEntityManager()->flush();
		}
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
