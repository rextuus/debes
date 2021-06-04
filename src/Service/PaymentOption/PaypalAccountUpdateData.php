<?php

namespace App\Service\PaymentOption;

use App\Entity\PaypalAccount;

class PaypalAccountUpdateData extends PaypalAccountData
{

    /**
     * initFromEntity
     *
     * @param PaypalAccount $paypalAccount
     *
     * @return $this
     */
    public function initFromEntity(PaypalAccount $paypalAccount): self
    {
        $this->setEnabled($paypalAccount->getEnabled());
        $this->setOwner($paypalAccount->getOwner());
        $this->setEmail($paypalAccount->getEmail());
        $this->setDescription($paypalAccount->getDescription());
        $this->setPreferred($paypalAccount->getIsPrioritised());
        return $this;
    }
}
