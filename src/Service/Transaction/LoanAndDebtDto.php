<?php

namespace App\Service\Transaction;

use App\Entity\Debt;
use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Service\Transaction\TransactionDtos\TransactionPartDtoInterface;
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
    private $transactionPartners;

    /**
     * @var string[]
     */
    private array $transactionPartnersDetails;

    /**
     * @var string
     */
    private $reason;

    /**
     * @var int
     */
    private $transactionId;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var array
     */
    private $exchangeDtos;

    /**
     * @var bool
     */
    private $isMultiple;

    /**
     * create
     *
     * @param TransactionPartInterface $transactionPartInterface
     * @param LoanAndDebtDto           $dto
     *
     * @return void
     */
    protected static function init(TransactionPartInterface $transactionPartInterface, LoanAndDebtDto $dto): void
    {
        $dto->setCreated($transactionPartInterface->getCreated());
        $dto->setEdited($transactionPartInterface->getEdited());
        $dto->setState($transactionPartInterface->getState());
        $dto->setReason($transactionPartInterface->getTransaction()->getReason());
        $dto->setTransactionId($transactionPartInterface->getTransaction()->getId());
        $dto->setSlug($transactionPartInterface->getTransaction()->getSlug());
        $dto->setExchangeDtos([]);
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
    public function getTransactionPartners(): string
    {
        return $this->transactionPartners;
    }

    /**
     * @param string $transactionPartners
     */
    public function setTransactionPartners(string $transactionPartners): void
    {
        $this->transactionPartners = $transactionPartners;
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

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return array
     */
    public function getExchangeDtos(): array
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
     * @return string[]
     */
    public function getTransactionPartnersDetails(): array
    {
        return $this->transactionPartnersDetails;
    }

    /**
     * @param string[] $transactionPartnersDetails
     */
    public function setTransactionPartnersDetails(array $transactionPartnersDetails): void
    {
        $this->transactionPartnersDetails = $transactionPartnersDetails;
    }

    /**
     * @return bool
     */
    public function isMultiple(): bool
    {
        return $this->isMultiple;
    }

    /**
     * @param bool $isMultiple
     */
    public function setIsMultiple(bool $isMultiple): void
    {
        $this->isMultiple = $isMultiple;
    }


}
