<?php

namespace App\Service\PaymentAction;

use App\Entity\BankAccount;
use App\Entity\Exchange;
use App\Entity\PaypalAccount;
use App\Entity\Transaction;

/**
 * PaymentActionData
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class PaymentActionData
{
    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @var string
     */
    private $variant;

    /**
     * @var Exchange|null
     */
    private $exchange;

    /**
     * @var BankAccount|null
     */
    private $bankAccountSender;

    /**
     * @var PaypalAccount|null
     */
    private $paypalAccountSender;

    /**
     * @var BankAccount|null
     */
    private $bankAccountReceiver;

    /**
     * @var PaypalAccount|null
     */
    private $paypalAccountReceiver;

    /**
     * @return Transaction
     */
    public function getTransaction(): Transaction
    {
        return $this->transaction;
    }

    /**
     * @param Transaction $transaction
     */
    public function setTransaction(Transaction $transaction): void
    {
        $this->transaction = $transaction;
    }

    /**
     * @return string
     */
    public function getVariant(): string
    {
        return $this->variant;
    }

    /**
     * @param string $variant
     */
    public function setVariant(string $variant): void
    {
        $this->variant = $variant;
    }

    /**
     * @return Exchange|null
     */
    public function getExchange(): ?Exchange
    {
        return $this->exchange;
    }

    /**
     * @param Exchange|null $exchange
     */
    public function setExchange(?Exchange $exchange): void
    {
        $this->exchange = $exchange;
    }

    /**
     * @return BankAccount|null
     */
    public function getBankAccountSender(): ?BankAccount
    {
        return $this->bankAccountSender;
    }

    /**
     * @param BankAccount|null $bankAccountSender
     */
    public function setBankAccountSender(?BankAccount $bankAccountSender): void
    {
        $this->bankAccountSender = $bankAccountSender;
    }

    /**
     * @return PaypalAccount|null
     */
    public function getPaypalAccountSender(): ?PaypalAccount
    {
        return $this->paypalAccountSender;
    }

    /**
     * @param PaypalAccount|null $paypalAccountSender
     */
    public function setPaypalAccountSender(?PaypalAccount $paypalAccountSender): void
    {
        $this->paypalAccountSender = $paypalAccountSender;
    }

    /**
     * @return BankAccount|null
     */
    public function getBankAccountReceiver(): ?BankAccount
    {
        return $this->bankAccountReceiver;
    }

    /**
     * @param BankAccount|null $bankAccountReceiver
     */
    public function setBankAccountReceiver(?BankAccount $bankAccountReceiver): void
    {
        $this->bankAccountReceiver = $bankAccountReceiver;
    }

    /**
     * @return PaypalAccount|null
     */
    public function getPaypalAccountReceiver(): ?PaypalAccount
    {
        return $this->paypalAccountReceiver;
    }

    /**
     * @param PaypalAccount|null $paypalAccountReceiver
     */
    public function setPaypalAccountReceiver(?PaypalAccount $paypalAccountReceiver): void
    {
        $this->paypalAccountReceiver = $paypalAccountReceiver;
    }
}