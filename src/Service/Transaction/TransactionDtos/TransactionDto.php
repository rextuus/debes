<?php

namespace App\Service\Transaction\TransactionDtos;

use App\Entity\Transaction;
use App\Service\Debt\DebtDto;
use DateTimeInterface;

/**
 * TransactionDto
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class TransactionDto
{
    /**
     * @var boolean
     */
    private $hasMultipleLoaners;

    /**
     * @var boolean
     */
    private $hasMultipleDebtors;

    /**
     * @var TransactionPartBaseDto[]
     */
    private $loanDtos;

    /**
     * @var TransactionPartBaseDto[]
     */
    private $debtDtos;

    /**
     * @var float
     */
    private $totalAmount;

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
    private $transactionSlug;

    /**
     * @var string
     */
    private $transactionPartner;

    /**
     * @var bool
     */
    private $isDebtVariant;

    /**
     * @return bool
     */
    public function isHasMultipleLoaners(): bool
    {
        return $this->hasMultipleLoaners;
    }

    /**
     * @param bool $hasMultipleLoaners
     */
    public function setHasMultipleLoaners(bool $hasMultipleLoaners): void
    {
        $this->hasMultipleLoaners = $hasMultipleLoaners;
    }

    /**
     * @return bool
     */
    public function isHasMultipleDebtors(): bool
    {
        return $this->hasMultipleDebtors;
    }

    /**
     * @param bool $hasMultipleDebtors
     */
    public function setHasMultipleDebtors(bool $hasMultipleDebtors): void
    {
        $this->hasMultipleDebtors = $hasMultipleDebtors;
    }

    /**
     * @return float
     */
    public function getTotalAmount(): float
    {
        return $this->totalAmount;
    }

    /**
     * @param float $totalAmount
     */
    public function setTotalAmount(float $totalAmount): void
    {
        $this->totalAmount = $totalAmount;
    }

    /**
     * @return string
     */
    public function getCreated(): string
    {
        return $this->created->format('d.n.Y');
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
    public function getTransactionSlug(): string
    {
        return $this->transactionSlug;
    }

    /**
     * @param string $transactionSlug
     */
    public function setTransactionSlug(string $transactionSlug): void
    {
        $this->transactionSlug = $transactionSlug;
    }

    /**
     * @return string
     */
    public function getTransactionPartner(): string
    {
        // debt variant
        if ($this->isDebtVariant){
            // multi
            if ($this->hasMultipleDebtors){

            }
            // single
            else{
                return $this->getLoanDtos()[0]->getOwner()->getFullName();
            }
        }
        // loan variant
        else{
            // multi
            if ($this->hasMultipleDebtors){

            }
            // single
            else{
                return $this->getDebtDtos()[0]->getOwner()->getFullName();
            }
        }
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
     * @return TransactionPartBaseDto[]
     */
    public function getLoanDtos(): array
    {
        return $this->loanDtos;
    }

    /**
     * @param TransactionPartBaseDto[] $loanDtos
     */
    public function setLoanDtos(array $loanDtos): void
    {
        $this->loanDtos = $loanDtos;
    }

    /**
     * @return TransactionPartBaseDto[]
     */
    public function getDebtDtos(): array
    {
        return $this->debtDtos;
    }

    /**
     * @param TransactionPartBaseDto[] $debtDtos
     */
    public function setDebtDtos(array $debtDtos): void
    {
        $this->debtDtos = $debtDtos;
    }

    /**
     * @return bool
     */
    public function isDebtVariant(): bool
    {
        return $this->isDebtVariant;
    }

    /**
     * @param bool $isDebtVariant
     */
    public function setIsDebtVariant(bool $isDebtVariant): void
    {
        $this->isDebtVariant = $isDebtVariant;
    }

    /**
     * @return TransactionPartBaseDto|null
     */
    public function getSingleTransactionPartner(): ?TransactionPartBaseDto
    {
        // debt variant
        if ($this->isDebtVariant && !$this->hasMultipleDebtors){
                return $this->getDebtDtos()[0];
        }
        // loan variant
        if (!$this->isDebtVariant && !$this->hasMultipleLoaners){
            return $this->getLoanDtos()[0];
        }
        return null;
    }

    /**
     * create
     *
     * @param Transaction $transaction
     * @param bool        $isDebtVariant
     *
     * @return TransactionDto
     */
    public static function createFromTransaction(Transaction $transaction, bool $isDebtVariant): TransactionDto
    {
        $dto = new self();

        $debts = $transaction->getDebts();
        $loans = $transaction->getLoans();
        $dto->setHasMultipleDebtors(count($debts) > 1);
        $dto->setHasMultipleLoaners(count($loans) > 1);

        $debtDtos = array();
        foreach ($debts as $debt){
            $debtDtos[] = TransactionPartBaseDto::createFromTransactionPart($debt, false);
        }
        $dto->setDebtDtos($debtDtos);

        $loanDtos = array();
        foreach ($loans as $loan){
            $loanDtos[] = TransactionPartBaseDto::createFromTransactionPart($loan, true);
        }
        $dto->setLoanDtos($loanDtos);

        $dto->setReason($transaction->getReason());
        $dto->setCreated($transaction->getCreated());
        $dto->setEdited($transaction->getEdited());
        $dto->setState($transaction->getState());
        $dto->setTotalAmount($transaction->getAmount());
        $dto->setTransactionId($transaction->getId());
        $dto->setTransactionSlug($transaction->getSlug());
        $dto->setIsDebtVariant($isDebtVariant);

        return $dto;
    }
}