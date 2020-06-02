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

    /** @return Team[] */
	public function findCompleteTeamsFromCompetition(Competition $competition): array
	{
		return $this->createQueryBuilder('t')
			->select('t', 'p')
			->join('t.players', 'p')
			->where('t.competition = :competition')
			->setParameter('competition', $competition)
			->getQuery()->execute();
    }

	public function save(Team $team, bool $flush = true)
	{
		$this->getEntityManager()->persist($team);
		if ($flush) {
			$this->getEntityManager()->flush();
		}
    }

    public function removeTeams(Collection $teams, bool $flush = true)
    {
        $teamNum = count($teams);
        for($i = 0; $i < $teamNum; $i++) {
            $this->getEntityManager()->remove($teams[$i]);
        }
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function hasTeamInCompetition(Competition $competition, Player $player) {
        for ($i = 0; $i < count($competition->getTeams()); $i++) {
            $team = $competition->getTeams()->toArray()[$i];
            if (in_array($player, $team->getPlayers()->toArray())) {
                return $team;
            }
        }
        return false;
    }

    // /**
    //  * @return Team[] Returns an array of Team objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('t.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Team
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
