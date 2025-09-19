<?php

namespace App\Tests\Port\Cli;

use App\Domain\BusinessConstants;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

class CalculateMonthlyMinimumFeesCommandTest extends KernelTestCase
{
    private Connection $connection;

    /**
     * @throws \Exception
     */
    protected function setUp(): void
    {
        $kernel = self::bootKernel();

        $this->connection = $kernel->getContainer()->get('doctrine.dbal.default_connection');
        $this->connection->beginTransaction();
    }

    /**
     * @throws \Exception
     */
    protected function tearDown(): void
    {
        parent::tearDown();

        if ($this->connection->isTransactionActive()) {
            $this->connection->rollBack();
        }
        $this->connection->close();
    }

    public function testExecute(): void
    {
        self::bootKernel();
        $application = new Application(self::$kernel);

        $command = $application->find('app:calculate-monthly-minimum-fees');
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'offset' => 1,
        ]);

        $commandTester->assertCommandIsSuccessful();

        $output = $commandTester->getDisplay();

        $calculationDate = (new \DateTimeImmutable('now'))->modify('-1 months');
        $this->assertStringContainsString('Calculating monthly minimum fees for date: '.$calculationDate->format(BusinessConstants::DATE_FORMAT), $output);

        // TODO: Create Fixtures and add more assertions to verify the monthly minimum fees calculations
    }
}
