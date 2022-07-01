<?php

namespace App\Service\Exchange;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;

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
     * @var Loan
     */
    private $loan;

    /**
     * @var Debt
     */
    private $debt;

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

    /**
     * @return Loan
     */
    public function getLoan(): Loan
    {
        return $this->loan;
    }

    /**
     * @param TransactionPartInterface $loan
     */
    public function setLoan(TransactionPartInterface $loan): void
    {
        $this->loan = $loan;
    }

    /**
     * @return Debt
     */
    public function getDebt(): Debt
    {
        return $this->debt;
    }

    /**
     * @param TransactionPartInterface $debt
     */
    public function setDebt(TransactionPartInterface $debt): void
    {
        $this->debt = $debt;
    }
}