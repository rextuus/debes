<?php

namespace App\Service\Transfer;

use App\Entity\Loan;
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
     * @var Loan|null
     */
    private $loan;

    /**
     * @return Loan|null
     */
    public function getLoan(): ?Loan
    {
        return $this->loan;
    }

    /**
     * @param Loan|null $loan
     */
    public function setLoan(?Loan $loan): void
    {
        $this->loan = $loan;
    }
}