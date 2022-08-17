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
           $infos = [];
           foreach ($debt->getTransaction()->getLoans() as $loan){
               $info = sprintf(
                   "%s:\t%.2f €",
                   $loan->getOwner()->getFullName(),
                   $loan->getInitialAmount()
               );
               $infos[] = $info;
           }
           $dto->setTransactionPartnersDetails($infos);
           $dto->setIsMultiple(true);
       }else{
           $dto->setTransactionPartners($debt->getTransaction()->getLoaner()->getFullName());
           $dto->setIsMultiple(false);
       }

        $dto->setAmount($debt->getAmount());

        parent::init($debt, $dto);

        return $dto;
    }
}