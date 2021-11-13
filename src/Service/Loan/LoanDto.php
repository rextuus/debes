<?php

namespace App\Service\Loan;

use App\Entity\Transaction;
use App\Service\Transaction\LoanAndDebtDto;

/**
 * LoanDto
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class LoanDto extends LoanAndDebtDto
{
    /**
     * create
     *
     * @param Transaction $transaction
     *
     * @return LoanDto
     */
    public static function create(Transaction $transaction): LoanDto
    {
        $dto = new self();

        $debt = $transaction->getDebts()[0];
        $loan = $transaction->getLoans()[0];
        $dto->setAmount($loan->getAmount());
        $dto->setTransactionPartner($debt->getOwner()->getFullName());

        parent::init($transaction, $dto);

        return $dto;
    }
}