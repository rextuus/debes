<?php

namespace App\Service\Debt;

use App\Entity\Transaction;
use App\Entity\User;
use DateTime;

class DebtData
{
    /**
     * @var float
     */
    private $amount;

    /**
     * @var DateTime
     */
    private $created;

    /**
     * @var DateTime|null
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
     * @var boolean
     */
    private $paid;

    /**
     * @var string
     */
    private $reason;

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
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * @param DateTime $created
     */
    public function setCreated(DateTime $created): void
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
     * @return DateTime|null
     */
    public function getEdited(): ?DateTime
    {
        return $this->edited;
    }

    /**
     * @param DateTime|null $edited
     */
    public function setEdited(?DateTime $edited): void
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
     * initFrom
     *
     * @param $loan
     *
     * @return $this
     */
    public function initFrom($loan): DebtData
    {
        $this->setCreated($loan->getCreated());
        $this->setAmount($loan->getAmount());
        $this->setCreated($loan->getCreated());
        $this->setOwner($loan->getOwner());
        $this->setPaid($loan->getPaid());
        $this->setTransaction($loan->getTransaction());

        return $this;
    }
}
