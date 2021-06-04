<?php

namespace App\Service\Transaction;

use App\Entity\Transaction;
use DateTime;
use DateTimeInterface;

class TransactionUpdateData extends TransactionData
{
    /**
     * @var DateTime
     */
    private $created;

    /**
     * @var DateTimeInterface|null
     */
    private $edited;

    /**
     * @var string
     */
    private $state;

    /**
     * @return DateTimeInterface
     */
    public function getCreated(): DateTimeInterface
    {
        return $this->created;
    }

    /**
     * @param DateTime $created
     */
    public function setCreated(DateTimeInterface $created): void
    {
        $this->created = $created;
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

    /**
     * initFrom
     *
     * @param Transaction $transaction
     *
     * @return TransactionUpdateData
     */
    public function initFrom(Transaction $transaction): TransactionUpdateData
    {
        $this->setReason($transaction->getReason());
        $this->setState($transaction->getState());
        $this->setAmount($transaction->getAmount());
        $this->setDebts($transaction->getDebts());
        $this->setLoans($transaction->getLoans());
        $this->setCreated($transaction->getCreated());
        $this->setEdited($transaction->getEdited());
        return $this;
    }
}
