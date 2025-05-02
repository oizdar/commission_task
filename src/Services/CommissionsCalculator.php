<?php

declare(strict_types=1);

namespace App\CommissionTask\Services;

use App\CommissionTask\Enums\ConfigName;
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
    public const int CONFIG_AMOUNT_MULTIPLIER = 100;
    private Money $weeklyFreeOfChargeAmount;
    private CommissionsCollection $commissions;
    private string $withdrawCommissionCurrencyCode;

    public function __construct(private ExchangeRatesClient $exchangeRatesClient)
    {
        $config = ConfigService::getInstance();
        $this->withdrawCommissionCurrencyCode = (string) $config->get(ConfigName::WithdrawCommissionCurrency);
        if (empty($this->withdrawCommissionCurrencyCode)) {
            throw new \RuntimeException('Withdraw commission currency is not set in the configuration');
        }

        $this->weeklyFreeOfChargeAmount = new Money(
            (int) $config->get(ConfigName::WeeklyFreeOfChargeAmount) * self::CONFIG_AMOUNT_MULTIPLIER,
            new Currency($this->withdrawCommissionCurrencyCode)
        );
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
        return MoneyHelper::multiplyPercentageRoundUp($operation->amount, (float) ConfigService::getInstance()->get(ConfigName::DepositCommissionPercentage));
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

        $config = ConfigService::getInstance();
        $privateWithdrawCommissionPercentage = (float) $config->get(ConfigName::PrivateWithdrawCommissionPercentage);

        /*
         * If the user has already made all free withdrawals this week, we charge the full commission.
         */
        if ($userWeeklyOperations->count() >= $config->get(ConfigName::WeeklyFreeOfChargeTransactions)) {
            return MoneyHelper::multiplyPercentageRoundUp($operation->amount, $privateWithdrawCommissionPercentage);
        }

        /**
         * We need to convert the weekly operations sum to the configured currency, amount limit is calculated only in the configured currency.
         */
        $converter = new Converter(new ISOCurrencies(), $this->exchangeRatesClient->getRates());
        $weeklyOperationsSum = $userWeeklyOperations->getSumInCurrency($converter, new Currency($this->withdrawCommissionCurrencyCode));

        /*
         * If previous user withdrawals exceed the free amount, we charge the full commission.
         */
        if ($weeklyOperationsSum->greaterThan($this->weeklyFreeOfChargeAmount)) {
            return MoneyHelper::multiplyPercentageRoundUp($operation->amount, $privateWithdrawCommissionPercentage);
        }

        /**
         * If the user withdrawals are less than the free amount, we calculate the commission for the rest of the amount.
         */
        $remainingFreeAmount = $this->weeklyFreeOfChargeAmount->subtract($weeklyOperationsSum);
        if (!$remainingFreeAmount->getCurrency()->equals($operation->amount->getCurrency())) {
            $remainingFreeAmount = $converter->convert($remainingFreeAmount, $operation->amount->getCurrency());
        }

        $valueToCalculateCommission = $operation->amount->subtract($remainingFreeAmount);

        /*
         * If the user withdrawals are less than the free amount, we calculate the commission for the rest of the amount.
         */
        if ($valueToCalculateCommission->greaterThan(MoneyHelper::zeroValue($operation->amount->getCurrency()))) {
            return MoneyHelper::multiplyPercentageRoundUp($valueToCalculateCommission, $privateWithdrawCommissionPercentage);
        }

        /*
         * If the user withdrawals are less than the free amount, we don't charge any commission.
         */
        return MoneyHelper::zeroValue($operation->amount->getCurrency());
    }

    private function calculateCommissionForBusinessWithdraw(Operation $operation): Money
    {
        return MoneyHelper::multiplyPercentageRoundUp($operation->amount, (float) ConfigService::getInstance()->get(ConfigName::BusinessWithdrawCommissionPercentage));
    }
}
