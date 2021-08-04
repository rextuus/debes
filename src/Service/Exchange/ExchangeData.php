<?php

namespace App\Service\Exchange;

use App\Entity\Transaction;

/**
 * ExchangeData
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class ExchangeData
{
    /**
     * @var float
     */
    private $remainingAmount;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var float
     */
    private $amount;

    /**
     * @return float
     */
    public function getRemainingAmount(): float
    {
        return $this->remainingAmount;
    }

    /**
     * @param float $remainingAmount
     */
    public function setRemainingAmount(float $remainingAmount): void
    {
        $this->remainingAmount = $remainingAmount;
    }

    /**
     * @return Transaction
     */
    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    /**
     * @param Transaction $transaction
     */
    public function setTransaction(Transaction $transaction): void
    {
        $this->transaction = $transaction;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }
}