<?php

namespace App\Service\Transaction;

use DateTimeInterface;

interface TransactionPartDataInterface
{
    public function setAmount(float $amount): void;

    public function getAmount(): float;

    public function setState(string $state): void;
}