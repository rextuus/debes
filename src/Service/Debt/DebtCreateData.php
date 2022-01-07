<?php

namespace App\Service\Debt;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Transaction\TransactionData;
use DateTime;

class DebtCreateData extends DebtData
{

    /**
     * initFromUser
     *
     * @param User $debtor
     *
     * @return DebtCreateData
     */
    public function initFromUser(User $debtor): self
    {
        $this->setOwner($debtor);
        return $this;
    }

    /**
     * initFromData
     *
     * @param TransactionData $data
     *
     * @return $this
     */
    public function initFromData(TransactionData $data): self
    {
        $this->setOwner($data->getOwner());
        $this->setAmount($data->getAmount());
        $this->setReason($data->getReason());
        $this->setCreated(new DateTime());
        $this->setEdited(new DateTime());
        $this->setState(Transaction::STATE_READY);
        $this->setPaid(false);
        return $this;
    }
}