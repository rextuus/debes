<?php

namespace App\Service\Exchange;

use App\Entity\Exchange;
use App\Entity\Transaction;
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * ExchangeFactory
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class ExchangeFactory
{
    public function createByData(ExchangeCreateData $exchangeData): Exchange
    {
        $exchange = $this->createNewExchangeInstance();
        $this->mapData($exchange, $exchangeData);

        return $exchange;
    }

    public function mapData(Exchange $exchange, ExchangeData $exchangeData)
    {
        $exchange->setCreated(new DateTime());
        $exchange->setRemainingAmount($exchangeData->getRemainingAmount());
        $exchange->setTransaction($exchangeData->getTransaction());
    }

    /**
     * createNewExchaneInstance
     *
     * @return Exchange
     */
    private function createNewExchangeInstance(): Exchange
    {
        return new Exchange();
    }
}