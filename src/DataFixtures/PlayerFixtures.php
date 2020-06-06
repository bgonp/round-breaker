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
        $admin->setUsername('admin');
        $admin->setEmail('admin@admin.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setTwitchName('admin');
        $admin->setPassword($this->encoder->encodePassword($admin, 'randompassword'));
        $faker = Faker\Factory::create();
        $this->playerRepository->save($admin, false);
        for ($i = 1; $i < 100; ++$i) {
            $player = new Player();
            do {
                $username = $faker->userName;
            } while ($this->playerRepository->findBy(['username' => $username]));
            $player->setUsername($username);
            do {
                $email = $faker->email;
            } while ($this->playerRepository->findBy(['username' => $username]));
            $player->setEmail($email);
            while ($this->playerRepository->findBy(['twitchName' => $username])) {
                $username = $faker->userName;
            }
            $player->setTwitchName($username);
            $player->setPassword($this->encoder->encodePassword($player, 'randompassword'));
            $this->playerRepository->save($player, false);
        }
        $this->playerRepository->flush();
    }
}
