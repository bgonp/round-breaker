<?php

namespace App\Repository;

use App\Entity\Competition;
use App\Entity\Player;
use App\Entity\Team;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ManagerRegistry;

/**
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
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

    /** @return Team[]|Collection */
    public function findIncompleteByCompetition(Competition $competition): array
    {
        return $this->createQueryBuilder('t')
            ->select('t', 'COUNT(p.id)')
            ->join('t.players', 'p')
            ->groupBy('t')
            ->having('COUNT(p.id) < :players')
            ->setParameter('players', $competition->getPlayersPerTeam())
            ->getQuery()->execute();
    }

	public function save(Team $team, bool $flush = true): void
	{
		$this->getEntityManager()->persist($team);
		if ($flush) {
			$this->getEntityManager()->flush();
		}
    }

    public function removeTeams(Collection $teams, bool $flush = true): void
    {
        $teamNum = count($teams);
        for($i = 0; $i < $teamNum; $i++) {
            $this->getEntityManager()->remove($teams[$i]);
        }
        if ($flush) {
            $this->flush();
        }
    }

    public function flush(): void
    {
        $this->getEntityManager()->flush();
    }
}
