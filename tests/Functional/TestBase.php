<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\DataFixtures\BracketFixtures;
use App\DataFixtures\CompetitionFixtures;
use App\DataFixtures\GameFixtures;
use App\DataFixtures\PlayerFixtures;
use App\DataFixtures\RegistrationFixtures;
use App\Entity\Player;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\SchemaTool;
use Doctrine\ORM\Tools\ToolsException;
use Liip\TestFixturesBundle\Test\FixturesTrait;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

abstract class TestBase extends WebTestCase
{
    use FixturesTrait;

    private ?KernelBrowser $client = null;

    private ?Router $router = null;

    private array $repositories = [];

    private static bool $initializedDatabase = false;

    public function setUp()
    {
        parent::setUp();
        $this->getBrowser();
        $this->initDatabase();
    }

    protected function request(string $method, string $url, array $getParams = [], array $postParams = []): Crawler
    {
        return $this->getBrowser()->request($method, $this->getUrl($url, $getParams), $postParams);
    }

    protected function submit(string $buttonId, array $params): Crawler
    {
        return $this->client->submitForm($buttonId, $params);
    }

    protected function response(): Response
    {
        return $this->getBrowser()->getResponse();
    }

    protected function followRedirect(): Crawler
    {
        return $this->getBrowser()->followRedirect();
    }

    private function getBrowser()
    {
        if (!$this->client) {
            $this->client = static::createClient();
        }

        return $this->client;
    }

    protected function login(Player $player = null): void
    {
        if (!$player) {
            $playerRepository = self::$container->get('App\Repository\PlayerRepository');
            $player = $playerRepository->findBy([], ['id' => 'DESC'])[0];
        }
        $this->client->loginUser($player);
    }

    protected function loginAsAdmin(): void
    {
        $playerRepository = self::$container->get('App\Repository\PlayerRepository');
        $admin = $playerRepository->findOneBy(['username' => 'admin']);
        $this->login($admin);
    }

    protected function getUrl(string $name, array $params = [])
    {
        if (!$this->router) {
            $this->router = self::$container->get('router');
        }

        return $this->router->generate($name, $params);
    }

    protected function reloadFixtures(): void
    {
        $this->postFixtureSetup();
        $this->loadFixtures([
            PlayerFixtures::class,
            GameFixtures::class,
            CompetitionFixtures::class,
            RegistrationFixtures::class,
            BracketFixtures::class,
        ]);
    }

    /**
     * @throws ToolsException
     */
    private function initDatabase(): void
    {
        if (!static::$initializedDatabase) {
            static::$initializedDatabase = true;

            $em = $this->getContainer()->get('doctrine')->getManager();
            $metadata = $em->getMetadataFactory()->getAllMetadata();

            $schemaTool = new SchemaTool($em);
            $schemaTool->dropDatabase();
            $schemaTool->createSchema($metadata);

            $this->reloadFixtures();
        }
    }

    protected function getRepository(string $entity): ServiceEntityRepository
    {
        if (!isset($this->repositories[$entity])) {
            $this->repositories[$entity] = self::$container->get("App\\Repository\\{$entity}Repository");
        }
        return $this->repositories[$entity];
    }
}
