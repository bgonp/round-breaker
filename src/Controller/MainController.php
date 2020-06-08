<?php

namespace App\Controller;

use App\Repository\CompetitionRepository;
use App\Repository\GameRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends BaseController
{
    /**
     * @Route("/", name="main", methods={"GET"})
     */
    public function main(
        Request $request,
        CompetitionRepository $competitionRepository,
        GameRepository $gameRepository
    ): Response {
        $competition = $competitionRepository->findOneRandomFinished();
        $competition = $competitionRepository->findCompleteById($competition->getId());
        if ($request->query->get('login')) {
            $session = new Session();
            $session->set('login', true);
            $referer = $request->headers->get('referer');
            if ($referer) {
                $refererPathInfo = Request::create($referer)->getPathInfo();
                $refererPathInfo = str_replace($request->getScriptName(), '', $refererPathInfo);
                if ('/' != $refererPathInfo) {
                    $session->set('referer', $refererPathInfo);
                }
            }
        }

        return $this->render('main/index.html.twig', [
            'last_username' => $request->query->get('last_username'),
            'last_email' => $request->query->get('last_email'),
            'last_twitchname' => $request->query->get('last_twitchname'),
            'competition' => $competition,
            'clickable' => false,
            'player' => $this->getPlayer(),
            'mostsPlayed' => $gameRepository->findMostPlayed(),
            'bracketType' => $competition->getIsOpen() ? 0 : $competition->getTeams()->count(),
        ]);
    }
}
