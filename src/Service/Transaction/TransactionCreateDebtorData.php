<?php

namespace App\Service\Transaction;

class TransactionCreateDebtorData
{
    use DebtorsTrait;

    /**
     * @var TransactionCreateData
     */
    private $transactionCreateData;

    /**
     * @var int
     */
    private $debtors;

    /**
     * @var int
     */
    private $loaners;

    /**
     * @return TransactionCreateData
     */
    public function getTransactionCreateData(): TransactionCreateData
    {
        return $this->transactionCreateData;
    }

    /**
     * @param TransactionCreateData $transactionCreateData
     */
    public function setTransactionCreateData(TransactionCreateData $transactionCreateData): void
    {
        $this->transactionCreateData = $transactionCreateData;
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

    /**
     * @param TransactionCreateData $data
     *
     * @return $this
     */
    public function initFromData(TransactionCreateData $data): TransactionCreateDebtorData
    {
        $this->setTransactionCreateData($data);
        $this->setDebtors($data->getDebtors());

        $debtorDatas = $this->getDebtorData();
        foreach ($debtorDatas as $debtorData){
            $debtorData->initFromUser($data->getRequester());
        }
        return $this;
    }
}