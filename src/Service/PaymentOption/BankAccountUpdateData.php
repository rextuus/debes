<?php

namespace App\Service\PaymentOption;

use App\Entity\BankAccount;

class BankAccountUpdateData extends BankAccountData
{

    /**
     * initFromEntity
     *
     * @param BankAccount $bankAccount
     *
     * @return $this
     */
    public function initFromEntity(BankAccount $bankAccount): self
    {
        $this->setEnabled($bankAccount->getEnabled());
        $this->setOwner($bankAccount->getOwner());
        $this->setIban($bankAccount->getIban());
        $this->setBic($bankAccount->getBic());
        $this->setAccountName($bankAccount->getAccountName());
        $this->setBankName($bankAccount->getBankName());
        return $this;
    }
}
