<?php

namespace App\Service\Debt;

use App\Entity\Transaction;
use App\Service\Transaction\LoanAndDebtDto;

/**
 * DebtDto
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class DebtDto extends LoanAndDebtDto
{
    /**
     * create
     * @param Transaction $transaction
     *
     * @return DebtDto
     */
    public static function create(Transaction $transaction): DebtDto
    {
        $dto = new self();

        $debt = $transaction->getDebts()[0];
        $loan = $transaction->getLoans()[0];
        $dto->setAmount($debt->getAmount());
        $dto->setTransactionPartner($loan->getOwner()->getFullName());

        parent::init($transaction, $dto);

        return $dto;
    }
}