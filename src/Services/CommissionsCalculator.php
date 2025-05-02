<?php

declare(strict_types=1);

namespace App\CommissionTask\Services;

use App\CommissionTask\Enums\OperationType;
use App\CommissionTask\Enums\UserType;
use App\CommissionTask\Helpers\MoneyHelper;
use App\CommissionTask\Models\Commission;
use App\CommissionTask\Models\CommissionsCollection;
use App\CommissionTask\Models\Operation;
use App\CommissionTask\Models\OperationsCollection;
use Money\Converter;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Money;

readonly class CommissionsCalculator
{
    // todo move constants to configuration
    public const float DEPOSIT_COMMISSION_PERCENTAGE = 0.03;
    public const float PRIVATE_WITHDRAW_COMMISSION_PERCENTAGE = 0.3;
    public const float BUSINESS_WITHDRAW_COMMISSION_PERCENTAGE = 0.5;
    public const int WEEKLY_FREE_OF_CHARGE_AMOUNT = 100000; // 1000.00 EUR
    public const string WITHDRAW_COMMISSION_CURRENCY = 'EUR';
    public const int WEEKLY_FREE_OF_CHARGE_TRANSACTIONS = 3;
    private Money $weeklyFreeOfChargeAmount;
    private CommissionsCollection $commissions;

    public function __construct(private ExchangeRatesClient $exchangeRatesClient)
    {
        $this->weeklyFreeOfChargeAmount = new Money(self::WEEKLY_FREE_OF_CHARGE_AMOUNT, new Currency(self::WITHDRAW_COMMISSION_CURRENCY));
        $this->commissions = new CommissionsCollection();
    }

    public function calculateCommissions(OperationsCollection $operations): CommissionsCollection
    {
        foreach ($operations as $operation) {
            $this->commissions->add($this->calculateCommissionFromOperation($operation));
        }

        return $this->commissions;
    }

    private function calculateCommissionFromOperation(Operation $operation): Commission
    {
        $commissionAmount = match ($operation->operationType) {
            OperationType::Deposit => $this->calculateDepositCommissionAmount($operation),
            OperationType::Withdraw => $this->calculateWithdrawCommissionAmount($operation),
        };

        return new Commission(
            $operation,
            $commissionAmount,
        );
    }

    private function calculateDepositCommissionAmount(Operation $operation): Money
    {
        return MoneyHelper::multiplyPercentageRoundUp($operation->amount, self::DEPOSIT_COMMISSION_PERCENTAGE);
    }

    private function calculateWithdrawCommissionAmount(Operation $operation): Money
    {
        return match ($operation->userType) {
            UserType::Private => $this->calculateCommissionForPrivateWithdraw($operation),
            UserType::Business => $this->calculateCommissionForBusinessWithdraw($operation),
        };
    }

    private function calculateCommissionForPrivateWithdraw(Operation $operation): Money
    {
        $userWeeklyOperations = $this->commissions->getWeeklyUserWithdrawals($operation);

        if ($userWeeklyOperations->count() >= self::WEEKLY_FREE_OF_CHARGE_TRANSACTIONS) {
            return MoneyHelper::multiplyPercentageRoundUp($operation->amount, self::PRIVATE_WITHDRAW_COMMISSION_PERCENTAGE);
        }

        $converter = new Converter(new ISOCurrencies(), $this->exchangeRatesClient->getRates());
        $weeklyOperationsSum = $userWeeklyOperations->getSumInCurrency($converter, new Currency(self::WITHDRAW_COMMISSION_CURRENCY));

        if ($weeklyOperationsSum->greaterThan($this->weeklyFreeOfChargeAmount)) {
            return MoneyHelper::multiplyPercentageRoundUp($operation->amount, self::PRIVATE_WITHDRAW_COMMISSION_PERCENTAGE);
        }

        $remainingFreeAmount = $this->weeklyFreeOfChargeAmount->subtract($weeklyOperationsSum);
        if (!$remainingFreeAmount->getCurrency()->equals($operation->amount->getCurrency())) {
            $remainingFreeAmount = $converter->convert($remainingFreeAmount, $operation->amount->getCurrency());
        }

        $valueToCalculateCommission = $operation->amount->subtract($remainingFreeAmount);

        if ($valueToCalculateCommission->greaterThan(MoneyHelper::zeroValue($operation->amount->getCurrency()))) {
            return MoneyHelper::multiplyPercentageRoundUp($valueToCalculateCommission, self::PRIVATE_WITHDRAW_COMMISSION_PERCENTAGE);
        }

        return MoneyHelper::zeroValue($operation->amount->getCurrency());


    }

    private function calculateCommissionForBusinessWithdraw(Operation $operation): Money
    {
        return MoneyHelper::multiplyPercentageRoundUp($operation->amount, self::BUSINESS_WITHDRAW_COMMISSION_PERCENTAGE);
    }
}
