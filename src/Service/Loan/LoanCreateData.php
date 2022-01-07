<?php

namespace App\Service\Loan;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Transaction\TransactionData;
use DateTime;

class LoanCreateData extends LoanData
{

    /**
     * initFromData
     *
     * @param TransactionData $data
     * @param User            $owner
     *
     * @return $this
     */
    public function initFromData(TransactionData $data, User $owner): self
    {
        $this->setAmount($data->getAmount());
        $this->setReason($data->getReason());
        $this->setCreated(new DateTime());
        $this->setEdited(new DateTime());
        $this->setOwner($owner);
        $this->setPaid(false);
        $this->setState(Transaction::STATE_READY);
        return $this;
    }
}