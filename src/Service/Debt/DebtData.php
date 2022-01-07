<?php

namespace App\Service\Debt;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\User;
use DateTime;
use DateTimeInterface;

class DebtData
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
     * @var User|null
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
     * @return User|null
     */
    public function getOwner(): ?User
    {
        return $this->owner;
    }

    /**
     * @param User|null $owner
     */
    public function setOwner(?User $owner): void
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
     * @return DateTimeInterface|null
     */
    public function getEdited(): ?DateTimeInterface
    {
        return $this->edited;
    }

    /**
     * @param DateTimeInterface|null $edited
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
     * @return string|null
     */
    public function getState(): ?string
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
     * @param Debt $debt
     *
     * @return $this
     */
    public function initFrom(Debt $debt): DebtData
    {
        $this->setCreated($debt->getCreated());
        $this->setAmount($debt->getAmount());
        $this->setInitialAmount($debt->getInitialAmount());
        $this->setOwner($debt->getOwner());
        $this->setPaid($debt->getPaid());
        $this->setTransaction($debt->getTransaction());
        $this->setEdited($debt->getEdited());
        $this->setState(Transaction::STATE_READY);

        return $this;
    }
}
