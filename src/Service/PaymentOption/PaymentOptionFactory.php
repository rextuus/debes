<?php

namespace App\Service\PaymentOption;

use App\Entity\BankAccount;
use App\Entity\PaymentOption;
use App\Entity\PaypalAccount;
use Exception;

class PaymentOptionFactory
{

    /**
     * createByData
     *
     * @param PaymentOptionData $paymentOptionData
     *
     * @return PaymentOption
     * @throws Exception
     */
    public function createByData(PaymentOptionData $paymentOptionData): PaymentOption
    {
        $paymentOption = $this->createNewPaymentOptionInstance($paymentOptionData);

        $this->mapData($paymentOption, $paymentOptionData);
        return $paymentOption;
    }

    /**
     * mapData
     *
     * @param PaymentOption     $paymentOption
     * @param PaymentOptionData $data
     *
     * @return void
     */
    public function mapData(PaymentOption $paymentOption, PaymentOptionData $data): void
    {
        //TODO there should only be one payment method of each variant that has value standard = true
        if ($data instanceof BankAccountData) {
            /** @var BankAccount $paymentOption */
            $paymentOption->setBankName($data->getBankName());
            $paymentOption->setAccountName($data->getAccountName());
            $paymentOption->setBic($data->getBic());
            $paymentOption->setIban($data->getIban());
        } elseif ($data instanceof PaypalAccountData) {
            /** @var PaypalAccount $paymentOption */
            $paymentOption->setEmail($data->getEmail());
        }

        $paymentOption->setOwner($data->getOwner());
        $paymentOption->setEnabled($data->isEnabled());
        $paymentOption->setIsPrioritised($data->isPreferred());
        $paymentOption->setDescription($data->getDescription());
    }

    /**
     * createNewPaymentOptionInstance
     *
     * @param PaymentOptionData $paymentOptionData
     *
     * @return PaymentOption
     * @throws Exception
     */
    public function createNewPaymentOptionInstance(PaymentOptionData $paymentOptionData): PaymentOption
    {
        $paymentOption = null;
        if ($paymentOptionData instanceof BankAccountData) {
            $paymentOption = new BankAccount();
        } elseif ($paymentOptionData instanceof PaypalAccountData) {
            $paymentOption = new PaypalAccount();
        }

        if (!$paymentOption){
            throw new Exception('Invalid PaymentOption');
        }

        return $paymentOption;
    }
}
