<?php

declare(strict_types=1);

namespace App\Service;

use App\Entity\Player;
use App\Exception\InvalidEmailException;
use App\Exception\InvalidPasswordException;
use App\Exception\InvalidPlayerDataException;
use App\Exception\InvalidTwitchNameException;
use App\Exception\InvalidUsernameException;
use App\Exception\PlayerAlreadyExistsException;
use App\Repository\PlayerRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PlayerService
{
    /**
     * @throws InvalidPlayerDataException|PlayerAlreadyExistsException
     */
    public function editPlayer(
        Player $player,
        Request $request,
        UserPasswordEncoderInterface $passwordEncoder,
        PlayerRepository $playerRepository,
        bool $new = false,
        bool $admin = false
    ): void {
        $username = $this->validUsername($request->request->get('username'));
        $password = ($password = $request->request->get('password')) || $new ? $this->validPassword($password) : null;
        $email = $this->validEmail($request->request->get('email'));
        $twitchName = $admin ? '' : $this->validTwitchName($request->request->get('twitch_name'));

        if ($new) {
            $existingFields = [];
            if ($playerRepository->findOneBy(['username' => $username])) {
                $existingFields[] = 'nombre de usuario';
            }
            if ($playerRepository->findOneBy(['twitchName' => $twitchName])) {
                $existingFields[] = 'nombre en Twitch';
            }
            if ($playerRepository->findOneBy(['email' => $email])) {
                $existingFields[] = 'e-mail';
            }
            if ($existingFields) {
                throw PlayerAlreadyExistsException::create($existingFields);
            }
        }

        $player
            ->setUsername($username)
            ->setEmail($email)
            ->setTwitchName($twitchName);
        if ($password) {
            $player->setPassword($passwordEncoder->encodePassword($player, $password));
        }
        $playerRepository->save($player);
    }

    private function validUsername(string $username = null): string
    {
        if (!preg_match('/^.{5,}$/', $username)) {
            throw InvalidUsernameException::create();
        }
        return $username;
    }

    private function validEmail(string $email = null): string
    {
        if (!$email = filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw InvalidEmailException::create();
        }
        return $email;
    }

    private function validPassword(string $password = null): string
    {
        if (!preg_match('/^.{6,}$/', $password)) {
            throw InvalidPasswordException::create();
        }
        return $password;
    }

    private function validTwitchName(string $twitchName = null): string
    {
        if (!preg_match('/^\w{6,}$/', $twitchName)) {
            throw InvalidTwitchNameException::create();
        }
        return $twitchName;
    }
}
