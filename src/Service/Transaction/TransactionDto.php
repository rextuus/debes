<?php

namespace App\Service\Transaction;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use DateTimeInterface;

/**
 * TransactionDto
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class TransactionDto
{
    const USER_IS_OWNER = 0;
    const USER_IS_CREATOR = 1;

    /**
     * @var Debt
     */
    private $debt;

    /**
     * @var Loan
     */
    private $loan;

    /**
     * @var int
     */
    private $amount;

    /**
     * @var string
     */
    private $state;

    /**
     * @var DateTimeInterface
     */
    private $edited;

    /**
     * @var int
     */
    private $direction;

    /**
     * @var string
     */
    private $transactionPartner;

    /**
     * @var int
     */
    private $transactionId;

    public static function create(Transaction $transaction, bool $useDebt): TransactionDto
    {
        $dto = new self();
        $dto->setState($transaction->getState());
        $dto->setEdited($transaction->getEdited());
        $dto->setTransactionId($transaction->getId());

        $debt = $transaction->getDebts()[0];
        $loan = $transaction->getLoans()[0];

        if ($useDebt) {
            $dto->setDirection(0);
            $dto->setAmount($debt->getAmount());
            $dto->setTransactionPartner($loan->getOwner()->getFirstName()." ".$loan->getOwner()->getLastName());
        } else {
            $dto->setDirection(1);
            $dto->setAmount($loan->getAmount());
            $dto->setTransactionPartner($debt->getOwner()->getFirstName() . ' ' . $debt->getOwner()->getLastName());
        }


        $dto->setDebt($debt);
        $dto->setLoan($loan);

        return $dto;
    }

    /**
     * @return Debt
     */
    public function getDebt(): Debt
    {
        return $this->debt;
    }

    /**
     * @param Debt $debt
     */
    public function setDebt(Debt $debt): void
    {
        $this->debt = $debt;
    }

    /**
     * @return Loan
     */
    public function getLoan(): Loan
    {
        return $this->loan;
    }

    /**
     * @param Loan $loan
     */
    public function setLoan(Loan $loan): void
    {
        $this->loan = $loan;
    }

    /**
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
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
     * @return int
     */
    public function getDirection(): int
    {
        return $this->direction;
    }

    /**
     * @param int $direction
     */
    public function setDirection(int $direction): void
    {
        $this->direction = $direction;
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
