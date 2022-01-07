<?php

namespace App\Entity;

use DateTimeInterface;

interface TransactionPartInterface
{
    public function getAmount(): ?float;

    public function getId(): ?int;

    public function setAmount(float $amount): TransactionPartInterface;

    public function getCreated(): ?DateTimeInterface;

    public function setCreated(DateTimeInterface $created): TransactionPartInterface;

    public function getEdited(): ?DateTimeInterface;

    public function setEdited(DateTimeInterface $edited): TransactionPartInterface;

    public function getPaid(): ?bool;

    public function setPaid(bool $paid): TransactionPartInterface;

    public function getTransaction(): ?Transaction;

    public function setTransaction(?Transaction $transaction): TransactionPartInterface;

    public function getOwner(): ?User;

    public function setOwner(?User $owner): TransactionPartInterface;

    public function getState(): string;

    public function setState(string $state): TransactionPartInterface;

    public function isLoan(): bool;

    public function isDebt(): bool;
}