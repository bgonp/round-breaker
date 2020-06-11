<?php

namespace App\Controller;

use App\Entity\Player;
use App\Exception\InvalidPlayerDataException;
use App\Repository\CompetitionRepository;
use App\Repository\GameRepository;
use App\Repository\PlayerRepository;
use App\Service\PlayerService;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class MainController extends BaseController
{
    use FixturesTrait;

    /**
     * @Route("/", name="main", methods={"GET", "POST"})
     */
    public function main(
        Request $request,
        CompetitionRepository $competitionRepository,
        GameRepository $gameRepository,
        SessionInterface $session
    ): Response {
        $competition = $competitionRepository->findOneRandomFinished(10);
        if ($competition) {
            $competition = $competitionRepository->findCompleteById($competition->getId());
        }
        if ($redirectTo = $request->request->get('redirect_to')) {
            $session->set('_security.main.target_path', $redirectTo);
        } elseif (!$request->query->get('last_username')) {
            $session->remove('_security.main.target_path');
        }

        return $this->render('main/index.html.twig', [
            'last_username' => $request->query->get('last_username'),
            'last_email' => $request->query->get('last_email'),
            'last_twitchname' => $request->query->get('last_twitchname'),
            'competition' => $competition,
            'clickable' => false,
            'player' => $this->getPlayer(),
            'mostsPlayed' => $gameRepository->findMostPlayed(),
            'bracketType' => $competition ? $competition->getTeams()->count() : 0,
        ]);
    }

    /**
     * @Route("/install", name="install", methods={"GET", "POST"})
     */
    public function install(
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        PlayerRepository $playerRepository,
        PlayerService $playerService
    ): Response {
        if (0 < $playerRepository->count([])) {
            return $this->redirectToRoute('main');
        }

        if ($request->isMethod('POST')) {
            $player = (new Player())->setRoles(['ROLE_ADMIN']);
            try {
                $playerService->editPlayer($player, $request, $passwordEncoder, $playerRepository, true, true);
                $this->addFlash('success', '¡Felicidades! Ahora inicia sesión y crea juegos para que los usuarios puedan organizar competiciones.');

                return $this->redirectToRoute('main');
            } catch (InvalidPlayerDataException $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->render('main/install.html.twig');
    }
}
