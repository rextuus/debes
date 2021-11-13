<?php

namespace App\Service\PaymentAction;

use App\Entity\PaymentAction;

/**
 * PaymentActionFactory
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class PaymentActionFactory
{
    /**
     * createByData
     *
     * @param PaymentActionData $paymentActionData
     *
     * @return PaymentAction
     */
    public function createByData(PaymentActionData $paymentActionData): PaymentAction
    {
        $paymentOption = $this->createNewPaymentOptionInstance();

        $this->mapData($paymentOption, $paymentActionData);
        return $paymentOption;
    }

    public function mapData(PaymentAction $paymentAction, PaymentActionData $data): void
    {
        $paymentAction->setTransaction($data->getTransaction());
        $paymentAction->setVariant($data->getVariant());
        $paymentAction->setExchange($data->getExchange());
        $paymentAction->setBankAccountSender($data->getBankAccountSender());
        $paymentAction->setPaypalAccountSender($data->getPaypalAccountSender());
        $paymentAction->setBankAccountReceiver($data->getBankAccountReceiver());
        $paymentAction->setPaypalAccountReceiver($data->getPaypalAccountReceiver());
    }

    /**
     * createNewPaymentOptionInstance
     *
     * @return PaymentAction
     */
    public function createNewPaymentOptionInstance(): PaymentAction
    {
        return new PaymentAction();
    }
}