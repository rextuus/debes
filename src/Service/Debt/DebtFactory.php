<?php

namespace App\Service\Debt;

use App\Entity\Debt;

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
        $debt->setCreated($data->getCreated());
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