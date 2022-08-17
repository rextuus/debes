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
            $infos = [];
            foreach ($loan->getTransaction()->getDebts() as $debt){
                $info = sprintf(
                    "%s:\t%.2f €",
                    $debt->getOwner()->getFullName(),
                    $debt->getInitialAmount()
                );
                $infos[] = $info;
            }
            $dto->setTransactionPartnersDetails($infos);
            $dto->setIsMultiple(true);
        }else{
            $dto->setTransactionPartners($loan->getTransaction()->getDebtor()->getFullName());
            $dto->setIsMultiple(false);
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