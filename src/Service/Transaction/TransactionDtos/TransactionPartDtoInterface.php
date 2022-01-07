<?php

namespace App\Service\Transaction\TransactionDtos;

interface TransactionPartDtoInterface
{
    /**
     * getAmount
     *
     * @return float
     */
    public function getAmount(): float;

    public function getReason(): float;
}