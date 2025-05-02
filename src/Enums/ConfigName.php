<?php

declare(strict_types=1);

namespace App\CommissionTask\Enums;

enum ConfigName: string
{
    case ExchangeRatesApiUrl = 'EXCHANGE_RATES_API_URL';
    case DepositCommissionPercentage = 'DEPOSIT_COMMISSION_PERCENTAGE';
    case PrivateWithdrawCommissionPercentage = 'PRIVATE_WITHDRAW_COMMISSION_PERCENTAGE';
    case BusinessWithdrawCommissionPercentage = 'BUSINESS_WITHDRAW_COMMISSION_PERCENTAGE';
    case WeeklyFreeOfChargeAmount = 'WEEKLY_FREE_OF_CHARGE_AMOUNT';
    case WithdrawCommissionCurrency = 'WITHDRAW_COMMISSION_CURRENCY';
    case WeeklyFreeOfChargeTransactions = 'WEEKLY_FREE_OF_CHARGE_TRANSACTIONS';
}
