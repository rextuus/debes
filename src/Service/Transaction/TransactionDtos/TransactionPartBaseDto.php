<?php

namespace App\Service\Transaction\TransactionDtos;

use App\Entity\Debt;
use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Entity\User;
use App\Service\Transaction\LoanAndDebtDto;
use DateTimeInterface;

/**
 * TransactionPartBaseDto
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class TransactionPartBaseDto
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
     * @var string
     */
    private $state;

    /**
     * @var array
     */
    private $exchangeDtos;

    /**
     * @var boolean
     */
    private $isLoanDto;

    /**
     * @var User
     */
    private $owner;

    /**
     * createFromTransactionPart
     *
     * @param TransactionPartInterface $transactionPart
     * @param bool                     $isLoanDto
     *
     * @return TransactionPartBaseDto
     */
    public static function createFromTransactionPart(
        TransactionPartInterface $transactionPart,
        bool $isLoanDto = true
    ): TransactionPartBaseDto {
        $dto = new self();
        $dto->setCreated($transactionPart->getCreated());
        $dto->setEdited($transactionPart->getEdited());
        $dto->setAmount($transactionPart->getAmount());
        $dto->setState($transactionPart->getState());
        $dto->setOwner($transactionPart->getOwner());
        $dto->setIsLoanDto($isLoanDto);

        return $dto;
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
    public function getState(): int
    {
        switch ($this->state) {
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
     * @return array|null
     */
    public function getExchangeDtos(): ?array
    {
        return $this->exchangeDtos;
    }

    /**
     * @param array $exchangeDtos
     */
    public function setExchangeDtos(array $exchangeDtos): void
    {
        $this->exchangeDtos = $exchangeDtos;
    }

    /**
     * @return bool
     */
    public function isLoanDto(): bool
    {
        return $this->isLoanDto;
    }

    /**
     * @param bool $isLoanDto
     */
    public function setIsLoanDto(bool $isLoanDto): void
    {
        $this->isLoanDto = $isLoanDto;
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
}
