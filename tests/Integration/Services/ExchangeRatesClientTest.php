<?php

namespace App\CommissionTask\Tests\Integration\Services;

use App\CommissionTask\Services\CommissionsCalculator;
use App\CommissionTask\Services\ExchangeRatesClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;


#[CoversClass(ExchangeRatesClient::class)]
class ExchangeRatesClientTest extends TestCase
{
    public function testGetRates(): void
    {
        $exchangeRatesClient =  new ExchangeRatesClient();
        $exchangeRatesClient->getRates();
        $this->assertEquals(CommissionsCalculator::WITHDRAW_COMMISSION_CURRENCY, $exchangeRatesClient->getBaseCurrency());
        $this->assertEquals((new \DateTimeImmutable())->format('Y-m-d'), $exchangeRatesClient->getLatestUpdateDate()?->format('Y-m-d'));

    }
}
