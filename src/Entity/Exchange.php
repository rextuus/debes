<?php

namespace App\Entity;

use App\Repository\ExchangeRepository;
use DateTimeInterface;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ExchangeRepository::class)
 */
class Exchange
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="float")
     */
    private $remainingAmount;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\ManyToOne(targetEntity=Debt::class, inversedBy="exchanges", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $debt;

    /**
     * @ORM\ManyToOne(targetEntity=Loan::class, inversedBy="exchanges", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $loan;

    /**
     * @ORM\ManyToOne(targetEntity=Transaction::class, inversedBy="exchanges")
     * @ORM\JoinColumn(nullable=false)
     */
    private $transaction;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCreated(): ?DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getRemainingAmount(): ?float
    {
        return $this->remainingAmount;
    }

    public function setRemainingAmount(float $remainingAmount): self
    {
        $this->remainingAmount = $remainingAmount;

        return $this;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): self
    {
        $this->amount = $amount;

        return $this;
    }

    public function getDebt(): ?Debt
    {
        return $this->debt;
    }

    public function setDebt(?Debt $debt): self
    {
        $this->debt = $debt;

        return $this;
    }

    public function getLoan(): ?Loan
    {
        return $this->loan;
    }

    public function setLoan(?Loan $loan): self
    {
        $this->loan = $loan;

        return $this;
    }
}
