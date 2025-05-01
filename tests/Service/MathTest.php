<?php

declare(strict_types=1);

namespace App\CommissionTask\Tests\Service;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use App\CommissionTask\Service\Math;

#[CoversClass(Math::class)]
class MathTest extends TestCase
{
    private Math $math;

    public function setUp(): void
    {
        $this->math = new Math(2);
    }

    /**
     * @param string $message
     * @param numeric-string $leftOperand
     * @param numeric-string $rightOperand
     * @param numeric-string $expectation
     * @return void
     */
    #[DataProvider('dataProviderForAddTesting')]
    public function testAdd(string $message, string $leftOperand, string $rightOperand, string $expectation): void
    {
        $this->assertEquals(
            $expectation,
            $this->math->add($leftOperand, $rightOperand),
            $message
        );
    }

    /**
     * @return array<int, array{0: string, 1: numeric-string, 2: numeric-string, 3: numeric-string}>
     */
    public static function dataProviderForAddTesting(): array
    {
        return [
            ['add 2 natural numbers', '1', '2', '3.00'],
            ['add 2 float numbers', '1.01', '2.02', '3.03'],
            ['add 2 negative numbers', '-1', '-2', '-3.00'],
            ['add negative number to a negative', '-1', '-2', '-3.00'],
            ['add negative number to a natural number', '-1', '2', '1.00'],
            ['add natural number to a negative number', '1', '-2', '-1.00'],
            ['add float to a float', '1.01', '2.02', '3.03'],
            ['add float to a natural number', '1.01', '2', '3.01'],
            ['add float to a negative number', '1.01', '-2', '-0.99'],
            ['add float to a negative float number', '1.01', '-2.02', '-1.01'],
            ['add natural number to a float number', '1', '2.02', '3.02'],
            ['add natural number to a negative float number', '1', '-2.02', '-1.02'],
            ['add negative float number to a natural number', '-1.01', '2.02', '1.01'],
        ];
    }
}
