<?php

namespace App\CommissionTask\Tests\Command;

use App\CommissionTask\Commands\CalculateCommissionsFromFile;
use App\CommissionTask\Helpers\MoneyHelper;
use App\CommissionTask\Models\Commission;
use App\CommissionTask\Models\CommissionsCollection;
use App\CommissionTask\Models\Operation;
use App\CommissionTask\Models\OperationsCollection;
use App\CommissionTask\Services\CommissionsCalculator;
use App\CommissionTask\Services\DataMapper\CsvFileMapper;
use App\CommissionTask\Services\DataMapper\OperationsCSVDataMapper;
use App\CommissionTask\Services\ExchangeRatesClient;
use Money\Exchange\FixedExchange;
use Money\Exchange\ReversedCurrenciesExchange;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(CalculateCommissionsFromFile::class)]
#[UsesClass(MoneyHelper::class)]
#[UsesClass(ExchangeRatesClient::class)]
#[UsesClass(CommissionsCollection::class)]
#[UsesClass(OperationsCollection::class)]
#[UsesClass(Commission::class)]
#[UsesClass(Operation::class)]
#[UsesClass(CommissionsCalculator::class)]
#[UsesClass(CsvFileMapper::class)]
#[UsesClass(OperationsCSVDataMapper::class)]
class CalculateCommissionCommandTest extends TestCase
{
    private ExchangeRatesClient $exchangeRatesClientMock;

    protected function setUp(): void
    {
        parent::setUp();

        $exchangeRatesClientMock = $this->createMock(ExchangeRatesClient::class);
        $exchangeRatesClientMock
            ->method('getRates')
            ->willReturn(new ReversedCurrenciesExchange(new FixedExchange([
                'EUR' => [
                    'USD' => '1.1497',
                    'JPY' => '129.53',
                ],
            ])));

        $this->exchangeRatesClientMock = $exchangeRatesClientMock;
    }

    public function testCommandExecution(): void
    {
        $command = new CalculateCommissionsFromFile(exchangeRatesClient: $this->exchangeRatesClientMock);
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
