<?php

namespace App\Service\Transaction;

use App\Service\Debt\DebtCreateData;

/**
 * TransactionCreateMultipleData
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class TransactionCreateMultipleData
{
    /**
     * @var DebtCreateData[]
     */
    private $debtorsData = [];

    /**
     * @var int
     */
    private $debtors;

    /**
     * @var float
     */
    private $completeAmount;

    /**
     * @var string
     */
    private $reason;

    /**
     * getProfessions
     *
     * @return DebtCreateData[]
     */
    public function getDebtorsData(): array
    {
        return $this->debtorsData;
    }

    /**
     * setProfessions
     *
     * @param DebtCreateData[] $debtorsData
     *
     * @return TransactionCreateMultipleData
     */
    public function setDebtorsData($debtorsData): TransactionCreateMultipleData
    {
        $this->debtorsData = $debtorsData;

        return $this;
    }



    /**
     * @return int
     */
    public function getDebtors(): int
    {
        return $this->debtors;
    }

    /**
     * @param int $debtors
     */
    public function setDebtors(int $debtors): void
    {
        $this->debtors = $debtors;
    }

    /**
     * @return float
     */
    public function getCompleteAmount(): float
    {
        return $this->completeAmount;
    }

    /**
     * @param float $completeAmount
     */
    public function setCompleteAmount(float $completeAmount): void
    {
        $this->completeAmount = $completeAmount;
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
}
