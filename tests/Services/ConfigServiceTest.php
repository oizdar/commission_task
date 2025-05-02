<?php

namespace App\CommissionTask\Tests\Services;

use App\CommissionTask\Enums\ConfigName;
use App\CommissionTask\Services\ConfigService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigService::class)]
class ConfigServiceTest extends TestCase
{
    public function testGetConfigsByEnum(): void
    {
        $configService = ConfigService::getInstance();
        $configService->loadConfiguration();

        $this->assertEquals(0.5, $configService->get(ConfigName::BusinessWithdrawCommissionPercentage));
        $this->assertEquals(0.3, $configService->get(ConfigName::PrivateWithdrawCommissionPercentage));
        $this->assertEquals(0.03, $configService->get(ConfigName::DepositCommissionPercentage));
        $this->assertEquals(1000, $configService->get(ConfigName::WeeklyFreeOfChargeAmount)); // 1000.00
        $this->assertEquals('EUR', $configService->get(ConfigName::WithdrawCommissionCurrency));
        $this->assertEquals(3, $configService->get(ConfigName::WeeklyFreeOfChargeTransactions));

    }
}
