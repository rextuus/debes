<?php

namespace App\Service\Transfer;

use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Service\Transaction\TransactionPartDataInterface;
use App\Service\Transaction\TransactionUpdateData;

class TransactionUpdateDataCollection
{
    private TransactionUpdateData $transactionHighData;

    private TransactionUpdateData $transactionLowData;

    private TransactionPartDataInterface $transactionPartLowDebtData;

    private TransactionPartDataInterface $transactionPartLowLoanData;

    private TransactionPartDataInterface $transactionPartHighDebtData;

    private TransactionPartDataInterface $transactionPartHighLoanData;

    private Transaction $transactionHigh;

    private Transaction $transactionLow;

    private TransactionPartInterface $transactionPartLowDebt;

    private TransactionPartInterface $transactionPartLowLoan;

    private TransactionPartInterface $transactionPartHighDebt;

    private TransactionPartInterface $transactionPartHighLoan;

    /**
     * @return TransactionUpdateData
     */
    public function getTransactionHighData(): TransactionUpdateData
    {
        return $this->transactionHighData;
    }

    /**
     * @param TransactionUpdateData $transactionHighData
     */
    public function setTransactionHighData(TransactionUpdateData $transactionHighData): void
    {
        $this->transactionHighData = $transactionHighData;
    }

    /**
     * @return TransactionUpdateData
     */
    public function getTransactionLowData(): TransactionUpdateData
    {
        return $this->transactionLowData;
    }

    /**
     * @param TransactionUpdateData $transactionLowData
     */
    public function setTransactionLowData(TransactionUpdateData $transactionLowData): void
    {
        $this->transactionLowData = $transactionLowData;
    }

    /**
     * @return TransactionPartDataInterface
     */
    public function getTransactionPartLowDebtData(): TransactionPartDataInterface
    {
        return $this->transactionPartLowDebtData;
    }

    /**
     * @param TransactionPartDataInterface $transactionPartLowDebtData
     */
    public function setTransactionPartLowDebtData(TransactionPartDataInterface $transactionPartLowDebtData): void
    {
        $this->transactionPartLowDebtData = $transactionPartLowDebtData;
    }

    /**
     * @return TransactionPartDataInterface
     */
    public function getTransactionPartLowLoanData(): TransactionPartDataInterface
    {
        return $this->transactionPartLowLoanData;
    }

    /**
     * @param TransactionPartDataInterface $transactionPartLowLoanData
     */
    public function setTransactionPartLowLoanData(TransactionPartDataInterface $transactionPartLowLoanData): void
    {
        $this->transactionPartLowLoanData = $transactionPartLowLoanData;
    }

    /**
     * @return TransactionPartDataInterface
     */
    public function getTransactionPartHighDebtData(): TransactionPartDataInterface
    {
        return $this->transactionPartHighDebtData;
    }

    /**
     * @param TransactionPartDataInterface $transactionPartHighDebtData
     */
    public function setTransactionPartHighDebtData(TransactionPartDataInterface $transactionPartHighDebtData): void
    {
        $this->transactionPartHighDebtData = $transactionPartHighDebtData;
    }

    /**
     * @return TransactionPartDataInterface
     */
    public function getTransactionPartHighLoanData(): TransactionPartDataInterface
    {
        return $this->transactionPartHighLoanData;
    }

    /**
     * @param TransactionPartDataInterface $transactionPartHighLoanData
     */
    public function setTransactionPartHighLoanData(TransactionPartDataInterface $transactionPartHighLoanData): void
    {
        $this->transactionPartHighLoanData = $transactionPartHighLoanData;
    }

    /**
     * @return Transaction
     */
    public function getTransactionHigh(): Transaction
    {
        return $this->transactionHigh;
    }

    /**
     * @param Transaction $transactionHigh
     */
    public function setTransactionHigh(Transaction $transactionHigh): void
    {
        $this->transactionHigh = $transactionHigh;
    }

    /**
     * @return Transaction
     */
    public function getTransactionLow(): Transaction
    {
        return $this->transactionLow;
    }

    /**
     * @param Transaction $transactionLow
     */
    public function setTransactionLow(Transaction $transactionLow): void
    {
        $this->transactionLow = $transactionLow;
    }

    /**
     * @return TransactionPartInterface
     */
    public function getTransactionPartLowDebt(): TransactionPartInterface
    {
        return $this->transactionPartLowDebt;
    }

    /**
     * @param TransactionPartInterface $transactionPartLowDebt
     */
    public function setTransactionPartLowDebt(TransactionPartInterface $transactionPartLowDebt): void
    {
        $this->transactionPartLowDebt = $transactionPartLowDebt;
    }

    /**
     * @return TransactionPartInterface
     */
    public function getTransactionPartLowLoan(): TransactionPartInterface
    {
        return $this->transactionPartLowLoan;
    }

    /**
     * @param TransactionPartInterface $transactionPartLowLoan
     */
    public function setTransactionPartLowLoan(TransactionPartInterface $transactionPartLowLoan): void
    {
        $this->transactionPartLowLoan = $transactionPartLowLoan;
    }

    /**
     * @return TransactionPartInterface
     */
    public function getTransactionPartHighDebt(): TransactionPartInterface
    {
        return $this->transactionPartHighDebt;
    }

    /**
     * @param TransactionPartInterface $transactionPartHighDebt
     */
    public function setTransactionPartHighDebt(TransactionPartInterface $transactionPartHighDebt): void
    {
        $this->transactionPartHighDebt = $transactionPartHighDebt;
    }

    /**
     * @return TransactionPartInterface
     */
    public function getTransactionPartHighLoan(): TransactionPartInterface
    {
        return $this->transactionPartHighLoan;
    }

    /**
     * @param TransactionPartInterface $transactionPartHighLoan
     */
    public function setTransactionPartHighLoan(TransactionPartInterface $transactionPartHighLoan): void
    {
        $this->transactionPartHighLoan = $transactionPartHighLoan;
    }

    /**
     * @param string $state
     * @return void
     */
    public function setStateTransactionHigh(string $state){
        $this->transactionHighData->setState($state);
    }

    /**
     * @param string $state
     * @return void
     */
    public function setStateTransactionLow(string $state){
        $this->transactionLowData->setState($state);
    }

    /**
     * @param string $state
     * @return void
     */
    public function setStateTransactionHighDebt(string $state){
        $this->transactionPartHighDebtData->setState($state);
    }

    /**
     * @param string $state
     * @return void
     */
    public function setStateTransactionHighLoan(string $state){
        $this->transactionPartHighLoanData->setState($state);
    }

    /**
     * @param string $state
     * @return void
     */
    public function setStateTransactionLowDebt(string $state){
        $this->transactionPartLowDebtData->setState($state);
    }

    /**
     * @param string $state
     * @return void
     */
    public function setStateTransactionLowLoan(string $state){
        $this->transactionPartLowLoanData->setState($state);
    }
}
