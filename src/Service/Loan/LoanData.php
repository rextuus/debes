<?php

namespace App\Service\Loan;

use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Entity\User;
use App\Service\Transaction\TransactionPartDataInterface;
use DateTime;
use DateTimeInterface;

class LoanData implements TransactionPartDataInterface
{

    /**
     * @var float
     */
    private $amount;

    /**
     * @var DateTimeInterface
     */
    private $created;

    /**
     * @var DateTimeInterface|null
     */
    private $edited;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var User
     */
    private $owner;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var boolean
     */
    private $paid;

    /**
     * @var string
     */
    private $state;

    /**
     * @var float
     */
    private $initialAmount;

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
     * @return DateTimeInterface
     */
    public function getCreated(): DateTimeInterface
    {
        return $this->created;
    }

    /**
     * @param DateTimeInterface $created
     */
    public function setCreated(DateTimeInterface $created): void
    {
        $this->created = $created;
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
     * @return User
     */
    public function getOwner(): User
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return DateTimeInterface|null
     */
    public function getEdited(): ?DateTimeInterface
    {
        return $this->edited;
    }

    /**
     * @param DateTime|null $edited
     */
    public function setEdited(?DateTimeInterface $edited): void
    {
        $this->edited = $edited;
    }

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
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->paid;
    }

    /**
     * @param bool $paid
     */
    public function setPaid(bool $paid): void
    {
        $this->paid = $paid;
    }

    /**
     * @return string
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * @param string $state
     */
    public function setState(string $state): void
    {
        $this->state = $state;
    }

    /**
     * @return float
     */
    public function getInitialAmount(): float
    {
        return $this->initialAmount;
    }

    /**
     * @param float $initialAmount
     */
    public function setInitialAmount(float $initialAmount): void
    {
        $this->initialAmount = $initialAmount;
    }

    /**
     * initFrom
     *
     * @param TransactionPartInterface $loan
     *
     * @return LoanData
     */
    public function initFrom(TransactionPartInterface $loan): LoanData
    {
        $this->setCreated($loan->getCreated());
        $this->setEdited($loan->getCreated());
        $this->setAmount($loan->getAmount());
        $this->setCreated($loan->getCreated());
        $this->setOwner($loan->getOwner());
        $this->setPaid($loan->getPaid());
        $this->setTransaction($loan->getTransaction());
        $this->setState($loan->getState());

        return $this;
    }
}
