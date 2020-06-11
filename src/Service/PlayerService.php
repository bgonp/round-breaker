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
        bool $new = false,
        bool $admin = false
    ): void {
        $username = $request->request->get('username');
        $plainPassword = $request->request->get('password');
        $email = $request->request->get('email');
        $twitchName = $request->request->get('twitch_name');

        $invalidFields = [];
        if (!$this->validUsername($username) || ($new && $playerRepository->findOneBy(['username' => $username]))) {
            $invalidFields[] = 'username';
        }
        if (!$admin && ((!$this->validTwitchName($twitchName)) || ($new && $playerRepository->findOneBy(['twitchName' => $twitchName])))) {
            $invalidFields[] = 'twitch name';
        }
        if (!$this->validEmail($email) || ($new && $playerRepository->findOneBy(['email' => $email]))) {
            $invalidFields[] = 'e-mail';
        }
        if ($new && !$this->validPassword($plainPassword)) {
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

    private function validUsername(string $username = null): bool
    {
        return $username ? (bool) preg_match('/^.{6,}$/', $username) : false;
    }

    private function validEmail(string $email = null): bool
    {
        return $email ? false !== filter_var($email, FILTER_VALIDATE_EMAIL) : false;
    }

    private function validPassword(string $password = null): bool
    {
        return $password ? (bool) preg_match('/^.{6,}$/', $password) : false;
    }

    private function validTwitchName(string $twitchName = null): bool
    {
        return $twitchName ? (bool) preg_match('/^\w{6,}$/', $twitchName) : false;
    }
}
