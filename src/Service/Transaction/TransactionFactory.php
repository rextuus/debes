<?php

namespace App\Service\Transaction;

use App\Entity\Transaction;
use DateTime;

class TransactionFactory
{

    /**
     * createByData
     *
     * @param TransactionData $transactionData
     *
     * @return Transaction
     */
    public function createByData(TransactionData $transactionData): Transaction
    {
        $transaction = $this->createNewTransactionInstance();
        $this->mapData($transaction, $transactionData);

        return $transaction;
    }

    /**
     * mapData
     *
     * @param Transaction     $transaction
     * @param TransactionData $data
     *
     * @return void
     */
    public function mapData(Transaction $transaction, TransactionData $data): void
    {
        if ($data instanceof TransactionCreateData){
            $transaction->setCreated(new DateTime());
            $transaction->setEdited($transaction->getCreated());
            $transaction->setInitialAmount($data->getAmount());
        }else{
            $transaction->setEdited(new DateTime());
        }

        $transaction->setAmount($data->getAmount());
        $transaction->setReason($data->getReason());
        $transaction->setState($data->getState());
    }

    /**
     * createNewTransactionInstance
     *
     * @return Transaction
     */
    private function createNewTransactionInstance(): Transaction
    {
        return new Transaction();
    }
}
