<?php

namespace App\Service\Loan;

use App\Entity\Loan;
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
     * @param Transaction $loan
     *
     * @return LoanDto
     */
    public static function create(Loan $loan): LoanDto
    {
        $dto = new self();

        if (count($loan->getTransaction()->getLoaners()) > 1){
            $dto->setTransactionPartners('Mehrere Gläubiger');
        }else{
            $dto->setTransactionPartners($loan->getTransaction()->getDebtor()->getFullName());
        }

        $dto->setAmount($loan->getAmount());

        parent::init($loan, $dto);

        return $dto;
    }

    /**
     * initFromLoan
     *
     * @param Loan $loan
     *
     * @return LoanDto
     */
    public static function initFromLoan(Loan $loan): LoanDto
    {
        $dto = new self();

        $dto->setAmount($loan->getAmount());
        $dto->setTransactionPartners($loan->getTransaction()->getDebtor()->getFullName());

        parent::init($loan->getTransaction(), $dto);

        return $dto;
    }
}