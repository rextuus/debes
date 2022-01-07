<?php

namespace App\Service\Debt;

use App\Entity\Debt;
use App\Entity\Transaction;

class DebtFactory
{
    /**
     * createByData
     *
     * @param DebtCreateData $DebtData
     *
     * @return Debt
     */
    public function createByData(DebtCreateData $DebtData): Debt
    {
        $debt = $this->createNewDebtInstance();
        $this->mapData($debt, $DebtData);

        return $debt;
    }

    /**
     * mapData
     *
     * @param Debt     $debt
     * @param DebtData $data
     *
     * @return void
     */
    public function mapData(Debt $debt, DebtData $data): void
    {
        if ($data instanceof  DebtCreateData){
            $debt->setCreated($data->getCreated());
            $debt->setEdited($data->getCreated());
            $debt->setState(Transaction::STATE_READY);
            $debt->setInitialAmount($data->getAmount());
        }else{
            $debt->setEdited($data->getEdited());
            $debt->setState($data->getState());
            $debt->setAmount($data->getAmount());
        }

        $debt->setAmount($data->getAmount());
        $debt->setOwner($data->getOwner());
        $debt->setTransaction($data->getTransaction());
        $debt->setPaid($data->isPaid());
    }

    /**
     * createNewDebtInstance
     *
     * @return Debt
     */
    private function createNewDebtInstance(): Debt
    {
        return new Debt();
    }
}