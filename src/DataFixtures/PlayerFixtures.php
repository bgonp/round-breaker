<?php

namespace App\DataFixtures;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PlayerFixtures extends Fixture
{
    private UserPasswordEncoderInterface $encoder;

    private PlayerRepository $playerRepository;

    public function __construct(UserPasswordEncoderInterface $encoder, PlayerRepository $playerRepository)
    {
        $this->encoder = $encoder;
        $this->playerRepository = $playerRepository;
    }

    public function load(ObjectManager $manager)
    {
        $admin = new Player();
        // Same encoded password to all users to prevent big loading fixture time.
        $encodedPassword = $this->encoder->encodePassword($admin, 'randompassword');
        $admin->setUsername('admin');
        $admin->setEmail('admin@admin.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setTwitchName('');
        $admin->setPassword($encodedPassword);
        $faker = Faker\Factory::create()->unique();
        $this->playerRepository->save($admin, false);
        for ($i = 1; $i < 100; ++$i) {
            $player = new Player();
            $username = $faker->userName;
            $player->setUsername($username);
            $player->setTwitchName($username);
            $player->setEmail($faker->email);
            $player->setPassword($encodedPassword);
            $this->playerRepository->save($player, false);
        }
        $this->playerRepository->flush();
    }
}
