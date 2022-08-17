<?php

namespace App\Service\Exchange;

use App\Entity\Exchange;
use DateTimeInterface;

/**
 * ExchangeDto
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class ExchangeDto
{
    /**
     * @var DateTimeInterface
     */
    private $created;

    /**
     * @var float
     */
    private $amount;

    /**
     * @var float
     */
    private $remainingAmount;

    /**
     * @var string
     */
    private $exchangeSlug;

    /**
     * @var string
     */
    private $exchangePartner;

    /**
     * @var string
     */
    private $exchangeReason;

    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $to;

    /**
     * create
     *
     * @param Exchange $exchange
     *
     * @return ExchangeDto
     */
    public static function create(Exchange $exchange, string $reason): ExchangeDto
    {
        $dto = new self();

        $dto->setFrom($exchange->getDebt()->getOwner()->getFullName());
        if ($exchange->getTransaction()->isSingleTransaction()){
            $dto->setTo($exchange->getLoan()->getTransaction()->getDebtor()->getFullName());
        }

        $dto->setExchangePartner($exchange->getTransaction()->getLoaner()->getFullName());
        $dto->setAmount($exchange->getAmount());
        $dto->setCreated($exchange->getCreated());
        $dto->setRemainingAmount($exchange->getRemainingAmount());
        $dto->setExchangeReason($reason);
        $dto->setExchangeSlug($exchange->getTransaction()->getSlug());

        return $dto;
    }

    /**
     * @return DateTimeInterface
     */
    public function getCreated(): DateTimeInterface
    {
        return $this->created;
    }

    /**
     * @param DateTimeInterface $created
     */
    public function setCreated(DateTimeInterface $created): void
    {
        $this->created = $created;
    }

    /**
     * @return float
     */
    public function getAmount(): float
    {
        return $this->amount;
    }

    /**
     * @param float $amount
     */
    public function setAmount(float $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return float
     */
    public function getRemainingAmount(): float
    {
        return $this->remainingAmount;
    }

    /**
     * @param float $remainingAmount
     */
    public function setRemainingAmount(float $remainingAmount): void
    {
        $this->remainingAmount = $remainingAmount;
    }

    /**
     * @return string
     */
    public function getExchangeSlug(): string
    {
        return $this->exchangeSlug;
    }

    /**
     * @param string $exchangeSlug
     */
    public function setExchangeSlug(string $exchangeSlug): void
    {
        $this->exchangeSlug = $exchangeSlug;
    }

    /**
     * @return string
     */
    public function getExchangePartner(): string
    {
        return $this->exchangePartner;
    }

    /**
     * @param string $exchangePartner
     */
    public function setExchangePartner(string $exchangePartner): void
    {
        $this->exchangePartner = $exchangePartner;
    }

    /**
     * @return string
     */
    public function getExchangeReason(): string
    {
        return $this->exchangeReason;
    }

    /**
     * @param string $exchangeReason
     */
    public function setExchangeReason(string $exchangeReason): void
    {
        $this->exchangeReason = $exchangeReason;
    }

    /**
     * getCreationDate
     *
     * @return string
     */
    public function getCreationDate(): string
    {
        return $this->created->format("d.m.Y");
    }

    /**
     * @return string
     */
    public function getFrom(): string
    {
        return $this->from;
    }

    /**
     * @param string $from
     */
    public function setFrom(string $from): void
    {
        $this->from = $from;
    }

    /**
     * @return string
     */
    public function getTo(): string
    {
        return $this->to;
    }

    /**
     * @param string $to
     */
    public function setTo(string $to): void
    {
        $this->to = $to;
    }
}
