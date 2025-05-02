<?php

namespace App\CommissionTask\Tests\Command;

use App\CommissionTask\Commands\CalculateCommissionsFromFile;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(CalculateCommissionsFromFile::class)]
class CalculateCommissionCommandTest extends TestCase
{
    public function testCommandExcecution(): void
    {
        $command = new CalculateCommissionsFromFile();
        $commandTester = new CommandTester($command);
        $commandTester->execute([
            'file' => __DIR__ . '/../resources/command_test.csv',
        ]);


        $output = $commandTester->getDisplay();
        $this->assertEquals(
            '0.60' . PHP_EOL .
            '3.00' . PHP_EOL .
            '0.00' . PHP_EOL .
            '0.06' . PHP_EOL .
            '1.50' . PHP_EOL .
            '0' . PHP_EOL .
            '0.70' . PHP_EOL .
            '0.30' . PHP_EOL .
            '0.30' . PHP_EOL .
            '3.00' . PHP_EOL .
            '0.00' . PHP_EOL .
            '0.00' . PHP_EOL .
            '8612' . PHP_EOL,
            $output,
        );
    }
}
