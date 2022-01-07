<?php

namespace App\Service\Debt;

use App\Entity\Debt;
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
     *
     * @param Debt $debt
     *
     * @return DebtDto
     */
    public static function create(Debt $debt): DebtDto
    {
        $dto = new self();

       if ($debt->getTransaction()->hasMultipleDebtors()){
           $dto->setTransactionPartners('Mehrere Gläubiger');
       }else{
           $dto->setTransactionPartners($debt->getTransaction()->getLoaner()->getFullName());
       }

        $dto->setAmount($debt->getAmount());

        parent::init($debt, $dto);

        return $dto;
    }
}