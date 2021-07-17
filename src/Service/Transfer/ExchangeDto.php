<?php

namespace App\Service\Transfer;

use App\Entity\Transaction;
use DateTimeInterface;
use phpDocumentor\Reflection\Types\String_;

/**
 * ExchangeDto
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class ExchangeDto
{
    /**
     * @var string
     */
    private $reason;

    /**
     * @var string
     */
    private $reasonExchange;

    /**
     * @var DateTimeInterface
     */
    private $created;

    /**
     * @var DateTimeInterface
     */
    private $createdExchange;

    /**
     * @var string
     */
    private $debtor;

    /**
     * @var string
     */
    private $loaner;

    /**
     * @var float
     */
    private $difference;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var float
     */
    private $amountExchange;

    /**
     * @return string
     */
    public function getReason(): string
    {
        return $this->reason;
    }

    /**
     * @param string $reason
     */
    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }

    /**
     * @return string
     */
    public function getReasonExchange(): string
    {
        return $this->reasonExchange;
    }

    /**
     * @param string $reasonExchange
     */
    public function setReasonExchange(string $reasonExchange): void
    {
        $this->reasonExchange = $reasonExchange;
    }

    /**
     * @return String
     */
    public function getCreated(): string
    {
        return $this->created->format('d.m.Y');
    }

    /**
     * @param DateTimeInterface $created
     */
    public function setCreated(DateTimeInterface $created): void
    {
        $this->created = $created;
    }

    /**
     * @return String
     */
    public function getCreatedExchange(): string
    {
        return $this->createdExchange->format('d.m.Y');
    }

    /**
     * @param DateTimeInterface $createdExchange
     */
    public function setCreatedExchange(DateTimeInterface $createdExchange): void
    {
        $this->createdExchange = $createdExchange;
    }

    /**
     * @return string
     */
    public function getDebtor(): string
    {
        return $this->debtor;
    }

    /**
     * @param string $debtor
     */
    public function setDebtor(string $debtor): void
    {
        $this->debtor = $debtor;
    }

    /**
     * @return string
     */
    public function getLoaner(): string
    {
        return $this->loaner;
    }

    /**
     * @param string $loaner
     */
    public function setLoaner(string $loaner): void
    {
        $this->loaner = $loaner;
    }

    /**
     * @return float
     */
    public function getDifference(): float
    {
        return $this->difference;
    }

    /**
     * @param float $difference
     */
    public function setDifference(float $difference): void
    {
        $this->difference = $difference;
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
     * @return float
     */
    public function getAmountExchange(): float
    {
        return $this->amountExchange;
    }

    /**
     * @param float $amountExchange
     */
    public function setAmountExchange(float $amountExchange): void
    {
        $this->amountExchange = $amountExchange;
    }


    /**
     * initFromTransactions
     *
     * @param Transaction $transaction
     * @param Transaction $transactionToExchange
     *
     * @return $this
     */
    public function initFromTransactions(
        Transaction $transaction,
        Transaction $transactionToExchange
    ): ExchangeDto {
        $this->setReason($transaction->getReason());
        $this->setReasonExchange($transactionToExchange->getReason());
        $this->setCreated($transaction->getCreated());
        $this->setCreatedExchange($transactionToExchange->getCreated());
        $this->setDebtor($transaction->getLoaner()->getFullName());
        $this->setLoaner($transactionToExchange->getLoaner()->getFullName());
        $this->setAmount($transaction->getAmount());
        $this->setAmountExchange($transactionToExchange->getAmount());
        return $this;
    }
}
