<?php

namespace App\Controller;

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
     * @Route("/main", name="main")
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
     * @Route("/main/createGame", name="create_game")
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
     * @Route("/main/createCompetition", name="create_competition")
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
     * @Route("/main/joinCompetition", name="join_competition")
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
     * @Route("/main/competitions/{competition}", name="view_competition")
     */
    public function viewCompetition(String $competition)
    {
        $entityManager = $this->getDoctrine()->getManager();
        return $this->render('main/viewCompetition.html.twig', [
            'controller_name' => 'MainController',
            'competition' => $entityManager->getRepository(Competition::Class)->findOneBy(['name' => $competition]),
        ]);
    }

    /**
     * @Route("/main/competitions/{competition}/randomize", name="randomize_teams")
     */
    public function randomizeTeams(String $competition)
    {
        $em = $this->getDoctrine()->getManager();
        $competition = $em->getRepository(Competition::Class)->findOneBy(['name' => $competition]);
        $user = $this->getUser()->getUsername();
        $user = $em->getRepository(Player::class)->findOneBy(['username' => $user]);
        if ($competition->getIsOpen() && $competition->getCreator() == $user && !$competition->getIsIndividual()) {
            $registrations = $competition->getRegistrations()->toArray();
            $teamNum = floor(count($registrations)/$competition->getPlayersPerTeam());
            $teamNum = $teamNum > $competition->getMaxPlayers()/$competition->getPlayersPerTeam() ? $competition->getMaxPlayers()/$competition->getPlayersPerTeam() : $teamNum;
            if ($teamNum % 2 != 0) {
                $teamNum = $teamNum-1;
            }
            $registrations = array_slice($registrations, 0, $competition->getPlayersPerTeam()*$teamNum);
            for($i = 0; $i < $teamNum; $i++) {
                $team = new Team();
                $team->setName("Team " . ($i+1));
                $team->setCompetition($competition);
                for($j = 0; $j < $competition->getPlayersPerTeam(); $j++) {
                    $randomRegistration = array_rand($registrations);
                    $team->addPlayer($registrations[$randomRegistration]->getPlayer());
                    unset($registrations[$randomRegistration]);
                }
                $em->persist($team);
            }
            $competition->setIsOpen(false);
            $em->persist($competition);
            $em->flush();
        }

        return $this->redirectToRoute('view_competition', array('competition' => $competition->getName()));
    }

    /**
     * @Route("/main/createTeam", name="create_team")
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
     * @Route("/main/joinTeam", name="join_team")
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