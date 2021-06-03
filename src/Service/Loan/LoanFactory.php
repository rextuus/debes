<?php

namespace App\Service\Loan;

use App\Entity\Loan;

class LoanFactory
{
    /**
     * createByData
     *
     * @param LoanCreateData $LoanData
     *
     * @return Loan
     */
    public function createByData(LoanCreateData $LoanData): Loan
    {
        $loan = $this->createNewLoanInstance();
        $this->mapData($loan, $LoanData);

        return $loan;
    }

    /**
     * mapData
     *
     * @param Loan     $loan
     * @param LoanData $data
     *
     * @return void
     */
    public function mapData(Loan $loan, LoanData $data): void
    {
        $loan->setCreated($data->getCreated());
        $loan->setAmount($data->getAmount());
        $loan->setOwner($data->getOwner());
        $loan->setTransaction($data->getTransaction());
        $loan->setPaid($data->isPaid());
    }

    /**
     * createNewTransactionInstance
     *
     * @return Loan
     */
    private function createNewLoanInstance(): Loan
    {
        return new Loan();
    }
}
