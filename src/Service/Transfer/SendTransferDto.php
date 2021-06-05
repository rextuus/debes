<?php

namespace App\Service\Transfer;

use App\Entity\BankAccount;
use App\Entity\PaymentOption;
use App\Entity\PaypalAccount;

/**
 * SendTransferDto
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class SendTransferDto
{
    /**
     * @var string
     */
    private $reason;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var string
     */
    private $iban;

    /**
     * @var string
     */
    private $bic;

    /**
     * @var string
     */
    private $bankOwner;

    /**
     * @var string
     */
    private $bankName;

    /**
     * @var string
     */
    private $paypalMail;

    /**
     * @var int
     */
    private $transactionId;

    /**
     * @return string
     */
    public function getReason(): string
    {
        $header = 'DEBES Transaktion Nr. ' . $this->getTransactionId().': ';
        return $header.$this->reason;
    }

    /**
     * @param string $reason
     */
    public function setReason(string $reason): void
    {
        $this->reason = $reason;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return string
     */
    public function getIban(): string
    {
        return $this->iban;
    }

    /**
     * @param string $iban
     */
    public function setIban(string $iban): void
    {
        $this->iban = $iban;
    }

    /**
     * @return string
     */
    public function getBic(): string
    {
        return $this->bic;
    }

    /**
     * @param string $bic
     */
    public function setBic(string $bic): void
    {
        $this->bic = $bic;
    }

    /**
     * @return string
     */
    public function getBankOwner(): string
    {
        return $this->bankOwner;
    }

    /**
     * @param string $bankOwner
     */
    public function setBankOwner(string $bankOwner): void
    {
        $this->bankOwner = $bankOwner;
    }

    /**
     * @return string
     */
    public function getBankName(): string
    {
        return $this->bankName;
    }

    /**
     * @param string $bankName
     */
    public function setBankName(string $bankName): void
    {
        $this->bankName = $bankName;
    }

    /**
     * @return string
     */
    public function getPaypalMail(): string
    {
        return $this->paypalMail;
    }

    /**
     * @param string $paypalMail
     */
    public function setPaypalMail(string $paypalMail): void
    {
        $this->paypalMail = $paypalMail;
    }

    /**
     * @return int
     */
    public function getTransactionId(): int
    {
        return $this->transactionId;
    }

    /**
     * @param int $transactionId
     */
    public function setTransactionId(int $transactionId): void
    {
        $this->transactionId = $transactionId;
    }

    /**
     * initFromBank
     *
     * @param PaymentOption $paymentOption
     *
     * @return $this
     */
    public function initFrom(PaymentOption $paymentOption): SendTransferDto
    {
        if ($paymentOption instanceof BankAccount) {
            $this->setBankName($paymentOption->getBankName());
            $this->setIban($paymentOption->getIban());
            $this->setBic($paymentOption->getBic());
            $this->setBankOwner($paymentOption->getAccountName());
        } elseif ($paymentOption instanceof PaypalAccount) {
            $this->setPaypalMail($paymentOption->getEmail());
        }
        return $this;
    }
}
