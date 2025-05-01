<?php

declare(strict_types=1);

namespace App\CommissionTask\Service\DataMapper;

use App\CommissionTask\Enums\OperationType;
use App\CommissionTask\Enums\UserType;
use App\CommissionTask\Models\Operation;

/**
 * @implements  MapperInterface<Operation>
 */
class OperationsCSVDataMapper implements MapperInterface
{
    public const int DATE_FIELD = 0;
    public const int USER_ID_FIELD = 1;
    public const int USER_TYPE_FIELD = 2;
    public const int OPERATION_TYPE_FIELD = 3;
    public const int AMOUNT_FIELD = 4;
    public const int CURRENCY_FIELD = 5;

    public function mapRow(array $row): Operation
    {
        try {
            return new Operation(
                date: new \DateTimeImmutable($row[self::DATE_FIELD] ?? ''),
                userId: $row[self::USER_ID_FIELD] ?? '',
                userType: UserType::from($row[self::USER_TYPE_FIELD] ?? ''),
                operationType: OperationType::from($row[self::OPERATION_TYPE_FIELD] ?? ''),
                amount: $row[self::AMOUNT_FIELD] ?? '',
                currency: $row[self::CURRENCY_FIELD] ?? '',
            );
        } catch (\Throwable $e) {
            throw new \InvalidArgumentException('Invalid data in CSV row: '.implode(',', $row), 0, $e);
        }
    }
}
