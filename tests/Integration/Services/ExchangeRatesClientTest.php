<?php

namespace App\CommissionTask\Tests\Integration\Services;

use App\CommissionTask\Enums\ConfigName;
use App\CommissionTask\Services\ConfigService;
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
        $this->assertEquals(ConfigService::getInstance()->get(ConfigName::WithdrawCommissionCurrency), $exchangeRatesClient->getBaseCurrency());
        $this->assertEquals((new \DateTimeImmutable())->format('Y-m-d'), $exchangeRatesClient->getLatestUpdateDate()?->format('Y-m-d'));

    }
}
