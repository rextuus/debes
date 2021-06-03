<?php

namespace App\Entity;

interface TransactionPartInterface
{
    public function getAmount(): ?float;
}