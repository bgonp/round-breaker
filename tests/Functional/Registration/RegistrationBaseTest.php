<?php

declare(strict_types=1);

namespace App\Tests\Functional\Registration;

use App\Entity\Competition;
use App\Tests\Functional\TestBase;

abstract class RegistrationBaseTest extends TestBase
{
    protected function getCompetition(bool $open, bool $finished): Competition
    {
        $competitionRepository = self::$container->get('App\Repository\CompetitionRepository');

        return $competitionRepository->findBy(['isOpen' => $open, 'isFinished' => $finished], ['id' => 'ASC'])[0];
    }
}
