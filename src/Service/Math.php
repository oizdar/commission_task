<?php

declare(strict_types=1);

namespace App\CommissionTask\Service;

class Math
{
    private int $scale;

    public function __construct(int $scale)
    {
        $this->scale = $scale;
    }

    /**
     * @param numeric-string $leftOperand
     * @param numeric-string $rightOperand
     *
     * @return numeric-string
     */
    public function add(string $leftOperand, string $rightOperand): string
    {
        return bcadd($leftOperand, $rightOperand, $this->scale);
    }
}
