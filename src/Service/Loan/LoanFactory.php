<?php

namespace App\Service\Loan;

use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Service\Debt\DebtCreateData;
use App\Service\Transaction\TransactionPartDataInterface;
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
     * @param TransactionPartInterface $loan
     * @param TransactionPartDataInterface $data
     *
     * @return void
     * @throws \Exception
     */
    public function mapData(TransactionPartInterface $loan, TransactionPartDataInterface $data): void
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
