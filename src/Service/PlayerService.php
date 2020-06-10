<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Player;
use App\Exception\InvalidPlayerDataException;
use App\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PlayerService
{
    /**
     * @throws InvalidPlayerDataException
     */
    public function editPlayer(
        Player $player,
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        PlayerRepository $playerRepository,
        bool $requiredPassword = true,
        bool $requiredTwitchName = true
    ): void {
        $username = $request->request->get('username');
        $plainPassword = $request->request->get('password');
        $email = $request->request->get('email');
        $twitchName = $request->request->get('twitchname');

        $invalidFields = [];
        if (!$username || $playerRepository->findOneBy(['username' => $username])) {
            $invalidFields[] = 'username';
        }
        if (($requiredTwitchName && !$twitchName) || $playerRepository->findOneBy(['twitchName' => $twitchName])) {
            $invalidFields[] = 'twitch name';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $playerRepository->findOneBy(['email' => $email])) {
            $invalidFields[] = 'e-mail';
        }
        if ($requiredPassword && !$plainPassword) {
            $invalidFields[] = 'password';
        }

        if ($invalidFields) {
            throw InvalidPlayerDataException::create($invalidFields);
        } else {
            $player
                ->setUsername($username)
                ->setEmail($email)
                ->setTwitchName($twitchName ?: '');
            if ($plainPassword) {
                $player->setPassword($passwordEncoder->encodePassword($player, $plainPassword));
            }
            $playerRepository->save($player);
        }
    }
}
