<?php

namespace App\Service\PaymentOption;

class BankAccountData extends PaymentOptionData
{

    /**
     * @string
     */
    private $iban;

    /**
     * @string
     */
    private $bic;

    /**
     * @string
     */
    private $accountName;

    /**
     * @string
     */
    private $bankName;

    /**
     * @return mixed
     */
    public function getIban()
    {
        return $this->iban;
    }

    /**
     * @param mixed $iban
     */
    public function setIban($iban): void
    {
        $this->iban = $iban;
    }

    /**
     * @return mixed
     */
    public function getBic()
    {
        return $this->bic;
    }

    /**
     * @param mixed $bic
     */
    public function setBic($bic): void
    {
        $this->bic = $bic;
    }

    /**
     * @return mixed
     */
    public function getAccountName()
    {
        return $this->accountName;
    }

    /**
     * @param mixed $accountName
     */
    public function setAccountName($accountName): void
    {
        $this->accountName = $accountName;
    }

    /**
     * @return mixed
     */
    public function getBankName()
    {
        return $this->bankName;
    }

    /**
     * @param mixed $bankName
     */
    public function setBankName($bankName): void
    {
        $this->bankName = $bankName;
    }
}
