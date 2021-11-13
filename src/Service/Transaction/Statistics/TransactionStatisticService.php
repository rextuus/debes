<?php

namespace App\Service\Transaction\Statistics;

use App\Entity\Transaction;

/**
 * TransactionStatisticService
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class TransactionStatisticService
{
    public function getTransactionBetweenUsers(Transaction $transaction): int
    {
        return 0;
    }

    public function getProblemsBetweenUsers(Transaction $transaction): int
    {
        return 0;
    }
}
