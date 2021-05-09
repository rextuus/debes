<?php

namespace App\Service\PaymentOption;

class PaymentOptionDTO
{

    /**
     * @boolean
     */
    private $isBankAccount;

    /**
     * @boolean
     */
    private $isPaypalAccount;

    /**
     * @boolean
     */
    private $enabled;

    /**
     * @int
     */
    private $accountId;

    /**
     * @return mixed
     */
    public function getIsBankAccount()
    {
        return $this->isBankAccount;
    }

    /**
     * @param mixed $isBankAccount
     */
    public function setIsBankAccount($isBankAccount): void
    {
        $this->isBankAccount = $isBankAccount;
    }

    /**
     * @return mixed
     */
    public function getIsPaypalAccount()
    {
        return $this->isPaypalAccount;
    }

    /**
     * @param mixed $isPaypalAccount
     */
    public function setIsPaypalAccount($isPaypalAccount): void
    {
        $this->isPaypalAccount = $isPaypalAccount;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @param mixed $accountId
     */
    public function setAccountId($accountId): void
    {
        $this->accountId = $accountId;
    }
}