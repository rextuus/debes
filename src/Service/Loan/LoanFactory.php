<?php

namespace App\Service\Loan;

use App\Entity\Loan;
use App\Entity\Transaction;
use App\Service\Debt\DebtCreateData;
use DateTime;

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
        if ($data instanceof  LoanCreateData) {
            $loan->setCreated($data->getCreated());
            $loan->setEdited($data->getCreated());
            $loan->setState(Transaction::STATE_READY);
            $loan->setInitialAmount($data->getAmount());
        }else{
            $loan->setEdited(new DateTime());
            $loan->setState($data->getState());
            $loan->setAmount($data->getAmount());
        }
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
