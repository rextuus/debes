<?php

namespace App\Service\Transaction;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\User;
use Doctrine\Common\Collections\Collection;

class TransactionData
{

    /**
     * @var float
     */
    private $amount;

    /**
     * @var Collection|Debt[]
     */
    private $debts;

    /**
     * @var Collection|Loan[]
     */
    private $loans;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var User|null
     */
    private $owner;

    /**
     * @var string|null
     */
    private $state;

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
     * @return Debt[]
     */
    public function getDebts(): array
    {
        return $this->debts;
    }

    /**
     * @param Collection|Debt[] $debts
     */
    public function setDebts(Collection $debts): void
    {
        $this->debts = $debts;
    }

    /**
     * @return Loan[]
     */
    public function getLoans(): array
    {
        return $this->loans;
    }

    /**
     * @param Collection|Loan[] $loans
     */
    public function setLoans(Collection $loans): void
    {
        $this->loans = $loans;
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
     * @return User|null
     */
    public function getOwner(): ?User
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
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     */
    public function setState(?string $state): void
    {
        $this->state = $state;
    }
}
