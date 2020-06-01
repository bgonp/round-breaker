<?php

namespace App\DataFixtures;

use App\Entity\Player;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class PlayerFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        for ($i = 1; $i < 50; $i++) {
            $player = new Player();
            $player->setUsername('Tester'.substr('0'.$i,-2));
            $player->setEmail($i.'t@gmail.com');
            $player->setPassword($this->encoder->encodePassword($player, 'randompassword'));
            if ($i === 1) $player->setTwitchName('vayaustecondioh');
            $manager->persist($player);
        }
        $admin = new Player();
        $admin->setUsername('admin');
        $admin->setEmail('admin@admin.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setPassword($this->encoder->encodePassword($admin, 'randompassword'));
        $manager->persist($admin);
        $manager->flush();
    }
}
