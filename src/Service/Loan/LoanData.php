<?php

namespace App\Service\Loan;

use App\Entity\Transaction;
use App\Entity\User;
use DateTime;

class LoanData
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
     * @var string
     */
    private $reason;

    /**
     * @var boolean
     */
    private $paid;

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


}
