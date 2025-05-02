<?php

declare(strict_types=1);

namespace App\CommissionTask\Service;

use App\CommissionTask\Enums\OperationType;
use App\CommissionTask\Enums\UserType;
use App\CommissionTask\Models\Commission;
use App\CommissionTask\Models\CommissionsCollection;
use App\CommissionTask\Models\Operation;
use App\CommissionTask\Models\OperationsCollection;
use Money\Converter;
use Money\Currencies\ISOCurrencies;
use Money\Currency;
use Money\Exchange\FixedExchange;
use Money\Exchange\ReversedCurrenciesExchange;
use Money\Money;

readonly class CommissionsCalculator
{
    // todo move constants to configuration
    public const int PERCENTAGE_DENOMINATOR = 100;
    public const float DEPOSIT_COMMISSION_PERCENTAGE = 0.03;
    public const float PRIVATE_WITHDRAW_COMMISSION_PERCENTAGE = 0.3;
    public const float BUSINESS_WITHDRAW_COMMISSION_PERCENTAGE = 0.5;

    public const int WEEKLY_FREE_OF_CHARGE_AMOUNT = 100000; // 1000.00 EUR
    public const string WITHDRAW_COMMISSION_CURRENCY = 'EUR';
    public const int WEEKLY_FREE_OF_CHARGE_TRANSACTIONS = 3;

    private Money $weeklyFreeOfChargeAmount;

    private CommissionsCollection $commissions;

    public function __construct(private OperationsCollection $operations)
    {
        $this->weeklyFreeOfChargeAmount = new Money(self::WEEKLY_FREE_OF_CHARGE_AMOUNT, new Currency(self::WITHDRAW_COMMISSION_CURRENCY));
        $this->commissions = new CommissionsCollection();
    }

    public function calculateCommissions(): CommissionsCollection
    {
        foreach ($this->operations as $operation) {
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
        return $operation->amount->multiply((string) (self::DEPOSIT_COMMISSION_PERCENTAGE / self::PERCENTAGE_DENOMINATOR), Money::ROUND_UP);
    }

    private function calculateWithdrawCommissionAmount(Operation $operation): Money
    {
        if ($operation->userType === UserType::Private) {
            return $this->calculateCommissionForPrivateWithdraw($operation);
        }

        return $this->calculateCommissionForBusinessWithdraw($operation);
    }

    private function calculateCommissionForPrivateWithdraw(Operation $operation): Money
    {
        $userWeeklyOperations = $this->commissions->getWeeklyUserWithdrawals($operation);

        if ($userWeeklyOperations->count() < self::WEEKLY_FREE_OF_CHARGE_TRANSACTIONS) {


            $converter = new Converter(new ISOCurrencies(), $exchange);

            $weeklyOperationsSum = $userWeeklyOperations->getSumInCurrency($converter, new Currency(self::WITHDRAW_COMMISSION_CURRENCY));

            if ($weeklyOperationsSum->greaterThan($this->weeklyFreeOfChargeAmount)) {
                return $operation->amount->multiply((string) (self::PRIVATE_WITHDRAW_COMMISSION_PERCENTAGE / self::PERCENTAGE_DENOMINATOR), Money::ROUND_UP);
            }

            $remainingFreeAmount = $this->weeklyFreeOfChargeAmount->subtract($weeklyOperationsSum);
            if (!$remainingFreeAmount->getCurrency()->equals($operation->amount->getCurrency())) {
                $remainingFreeAmount = $converter->convert($remainingFreeAmount, $operation->amount->getCurrency());
            }

            $valueToCalculateCommission = $operation->amount->subtract($remainingFreeAmount);

            if ($valueToCalculateCommission->greaterThan(new Money(0, $operation->amount->getCurrency()))) {
                return $valueToCalculateCommission->multiply((string) (self::PRIVATE_WITHDRAW_COMMISSION_PERCENTAGE / self::PERCENTAGE_DENOMINATOR), Money::ROUND_UP);
            }

            return new Money(0, $operation->amount->getCurrency());
        }

        return $operation->amount->multiply((string) (self::PRIVATE_WITHDRAW_COMMISSION_PERCENTAGE / self::PERCENTAGE_DENOMINATOR), Money::ROUND_UP);
    }

    private function calculateCommissionForBusinessWithdraw(Operation $operation): Money
    {
        return $operation->amount->multiply((string) (self::BUSINESS_WITHDRAW_COMMISSION_PERCENTAGE / self::PERCENTAGE_DENOMINATOR), Money::ROUND_UP);
    }
}
