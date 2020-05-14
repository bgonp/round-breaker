<?php

namespace App\Controller;

use App\Repository\CompetitionRepository;
use App\Repository\PlayerRepository;
use App\Repository\TeamRepository;
use App\Service\TeamService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Game;
use App\Entity\Player;
use App\Entity\Competition;
use App\Entity\Team;
use App\Entity\Registration;
use Symfony\Component\HttpFoundation\Request;

class MainController extends AbstractController
{
    /**
     * @Route("/", name="main", methods={"GET"})
     */
    public function main()
    {
        $entityManager = $this->getDoctrine()->getManager();
        return $this->render('main/index.html.twig', [
            'controller_name' => 'MainController',
            'games' => $entityManager->getRepository(Game::Class)->findAll(),
            'competitions' => $entityManager->getRepository(Competition::Class)->findAll()
        ]);
    }

    /**
     * @Route("/game/new", name="game_new", methods={"GET"})
     */
    public function createGame(Request $request)
    {

        if ($request->request->has('name')) {
            $em = $this->getDoctrine()->getManager();
            $game = $em->getRepository(Game::class)->findOneBy(['name' => $request->request->get('name')]);
            if (!$game) {
                $game = new Game();
                $game->setName($request->request->get('name'));
                $game->setDescription($request->request->get('description'));
                $em->persist($game);
                $em->flush();
            }
            return $this->redirectToRoute('main');
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            return $this->render('main/createGame.html.twig', [
                'controller_name' => 'MainController',
                'games' => $entityManager->getRepository(Game::Class)->findAll()
            ]);
        }
    }

    /**
     * @Route("/competition/new", name="competition_new")
     */
    public function createCompetition(Request $request)
    {

        if ($request->request->has('name')) {
            $em = $this->getDoctrine()->getManager();
            $competition = $em->getRepository(Competition::class)->findOneBy(['name' => $request->request->get('name')]);
            if (!$competition) {
                $competition = new Competition();
                $competition->setName($request->request->get('name'));
                $competition->setDescription($request->request->get('description'));
                $competition->setIsOpen(true);
                $competition->setIsFinished(false);
                $user = $this->getUser()->getUsername();
                $user = $em->getRepository(Player::class)->findOneBy(['username' => $user]);
                $competition->setCreator($user);
                $competition->setMaxPlayers($request->request->get('players'));
                $competition->setIsIndividual($request->request->get('individual') ? 1 : 0);
                $competition->setGame($em->getRepository(Game::class)->findOneBy(['name' => $request->request->get('game')]));
                $em->persist($competition);
                $em->flush();
            }
            return $this->redirectToRoute('main');
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            return $this->render('main/createCompetition.html.twig', [
                'controller_name' => 'MainController',
                'games' => $entityManager->getRepository(Game::Class)->findAll(),
                'competitions' => $entityManager->getRepository(Competition::Class)->findAll()
            ]);
        }
    }

    /**
     * @Route("/competition/join", name="competition_join")
     */
    public function joinCompetition(Request $request)
    {
        if ($request->request->has('competition')) {
            $em = $this->getDoctrine()->getManager();
            $competition = $em->getRepository(Competition::class)->findOneBy(['name' => $request->request->get('competition')]);
            //$team = $em->getRepository(Team::class)->findOneBy(['name' => $request->request->get('team')]);
            if (/*$team &&*/ $competition && count($competition->getRegistrations()) < $competition->getMaxPlayers()) {
                $registration = new Registration();
                $player = $em->getRepository(Player::class)->findOneBy(['username' => $this->getUser()->getUsername()]);
                $registration->setPlayer($player);
                $competition->addRegistration($registration);
                if ($competition->getIsIndividual()) {
                    $team = new Team();
                    $team->addPlayer($player);
                    $team->setName($player->getUsername());
                    $team->setCompetition($competition);
                    $em->persist($team);
                }
                //$competition->addTeam($team);
                $em->persist($registration);
                $em->persist($competition);
                $em->flush();
            }
            return $this->redirectToRoute('main');
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            return $this->render('main/joinCompetition.html.twig', [
                'controller_name' => 'MainController',
                'competitions' => $entityManager->getRepository(Competition::Class)->findAll(),
                'player' => $entityManager->getRepository(Player::class)->findOneBy(['username' => $this->getUser()->getUsername()])
            ]);
        }
    }

    /**
     * @Route("/competition/{id}", name="competition_show", methods={"GET"})
     */
    public function viewCompetition(
    	Competition $competition,
		CompetitionRepository $competitionRepository,
		TeamRepository $teamRepository
	) {
    	$teams = $teamRepository->findCompleteTeamsFromCompetition($competition);
        return $this->render('main/viewCompetition.html.twig', [
            'controller_name' => 'MainController',
            'competition' => $competition,
			'teams' => $teams
        ]);
    }

    /**
     * @Route("/competition/{id}/randomize", name="competition_randomize")
     */
    public function randomizeTeams(
    	Competition $competition,
		PlayerRepository $playerRepository,
		TeamService $teamService
	) {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser()->getUsername();
        $user = $playerRepository->findOneBy(['username' => $user]);
        $teamService->randomize($user, $competition);

        return $this->redirectToRoute('competition_show', ['id' => $competition->getId()]);
    }

    /**
     * @Route("/team/new", name="team_new")
     */
    public function createTeam(Request $request)
    {

        if ($request->request->has('name')) {
            $em = $this->getDoctrine()->getManager();
            $team = $em->getRepository(Team::class)->findOneBy(['name' => $request->request->get('name')]);
            if (!$team) {
                $team = new Team();
                $team->setName($request->request->get('name'));
                $em->persist($team);
                $user = $this->getUser()->getUsername();
                $user = $em->getRepository(Player::class)->findOneBy(['username' => $user]);
                $user->addTeam($team);
                $em->persist($user);
                $em->flush();
            }
            return $this->redirectToRoute('main');
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            return $this->render('main/createTeam.html.twig', [
                'controller_name' => 'MainController',
                'teams' => $entityManager->getRepository(Player::Class)->findAll()
            ]);
        }
    }

    /**
     * @Route("/team/join", name="team_join")
     */
    public function joinTeam(Request $request)
    {

        if ($request->request->has('team')) {
            $em = $this->getDoctrine()->getManager();
            $team = $em->getRepository(Team::class)->findOneBy(['name' => $request->request->get('team')]);
            if ($team) {
                $user = $this->getUser()->getUsername();
                $user = $em->getRepository(Player::class)->findOneBy(['username' => $user]);
                $user->addTeam($team);
                $em->persist($user);
                $em->flush();
            }
            return $this->redirectToRoute('main');
        } else {
            $entityManager = $this->getDoctrine()->getManager();
            return $this->render('main/joinTeam.html.twig', [
                'controller_name' => 'MainController',
                'teams' => $entityManager->getRepository(Team::Class)->findAll(),
                'players' => $entityManager->getRepository(Player::Class)->findAll()
            ]);
        }
    }
}