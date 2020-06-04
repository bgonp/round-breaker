<?php

namespace App\DataFixtures;

use App\Entity\Player;
use App\Repository\PlayerRepository;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
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
        for ($i = 1; $i < 50; $i++) {
            $player = new Player();
            $player->setUsername('Tester'.substr('0'.$i,-2));
            $player->setEmail($i.'t@gmail.com');
            $player->setPassword($this->encoder->encodePassword($player, 'randompassword'));
            if ($i === 1) $player->setTwitchName('vayaustecondioh');
            else $player->setTwitchName('tester'.substr('0'.$i,-2));
            $this->playerRepository->save($player, false);
        }
        $admin = new Player();
        $admin->setUsername('admin');
        $admin->setEmail('admin@admin.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setTwitchName('admin');
        $admin->setPassword($this->encoder->encodePassword($admin, 'randompassword'));
        $this->playerRepository->save($admin);
    }
}
