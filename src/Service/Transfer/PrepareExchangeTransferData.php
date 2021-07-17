<?php

namespace App\Service\Transfer;

use App\Entity\PaymentOption;
use App\Entity\Transaction;

/**
 * PrepareExchangeTransferData
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class PrepareExchangeTransferData
{
    /**
     * @var string|null
     */
    private $transactionSlug;

    /**
     * @return string|null
     */
    public function getTransactionSlug(): ?string
    {
        return $this->transactionSlug;
    }

    /**
     * @param string|null $transactionSlug
     */
    public function setTransactionSlug(?string $transactionSlug): void
    {
        $this->transactionSlug = $transactionSlug;
    }
}