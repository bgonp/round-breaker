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

    public function findOneByPlayerAndCompetition(Player $player, Competition $competition): ?Team
    {
        foreach ($competition->getTeams() as $team) {
            foreach ($team->getPlayers() as $teamPlayer) {
                if ($player->equals($teamPlayer)) {
                    return $team;
                }
            }
        }
        return null;
    }
}
