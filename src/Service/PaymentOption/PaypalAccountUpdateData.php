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
        return $this;
    }
}
