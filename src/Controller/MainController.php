<?php

namespace App\Controller;

use App\Repository\CompetitionRepository;
use App\Repository\GameRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends BaseController
{
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
}
