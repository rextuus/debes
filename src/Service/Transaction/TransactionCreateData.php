<?php

namespace App\Service\Transaction;

use App\Entity\User;
use App\Form\DebtCreateType;
use App\Service\Debt\DebtCreateData;

class TransactionCreateData extends TransactionData
{
    /**
     * @var int
     */
    private $debtors;

    /**
     * @var int
     */
    private $loaners;

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
     * @return int
     */
    public function getLoaners(): int
    {
        return $this->loaners;
    }

    /**
     * @param int $loaners
     */
    public function setLoaners(int $loaners): void
    {
        $this->loaners = $loaners;
    }
}
