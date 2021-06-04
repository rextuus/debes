<?php

namespace App\Service\Transaction;

use App\Entity\Transaction;
use App\Service\Loan\LoanDto;
use DateTimeInterface;

/**
 * LoanAndDebtDto
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
abstract class LoanAndDebtDto
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
     * @var int
     */
    private $state;

    /**
     * @var string
     */
    private $transactionPartner;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var int
     */
    private $transactionId;

    /**
     * create
     *
     * @param Transaction    $transaction
     * @param LoanAndDebtDto $dto
     *
     * @return void
     */
    protected static function init(Transaction $transaction, LoanAndDebtDto $dto): void
    {
        $dto->setCreated($transaction->getCreated());
        $dto->setEdited($transaction->getEdited());
        $dto->setState($transaction->getState());
        $dto->setReason($transaction->getReason());
        $dto->setTransactionId($transaction->getId());
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
     * @return string
     */
    public function getCreated(): string
    {
        return $this->created->format("d.m.Y");
    }

    /**
     * @param DateTimeInterface $created
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
     * @return string
     */
    public function getTransactionPartner(): string
    {
        return $this->transactionPartner;
    }

    /**
     * @param string $transactionPartner
     */
    public function setTransactionPartner(string $transactionPartner): void
    {
        $this->transactionPartner = $transactionPartner;
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        switch ($this->state){
            case Transaction::STATE_READY:
                return 1;
            case Transaction::STATE_ACCEPTED:
                return 2;
            case Transaction::STATE_CLEARED:
                return 3;
            case Transaction::STATE_CONFIRMED:
                return 4;
            default:
                return 0;
        }
    }

    /**
     * @param string $state
     */
    public function setState(string $state): void
    {
        $this->state = $state;
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
     * @return int
     */
    public function getTransactionId(): int
    {
        return $this->transactionId;
    }

    /**
     * @param int $transactionId
     */
    public function setTransactionId(int $transactionId): void
    {
        $this->transactionId = $transactionId;
    }
}
