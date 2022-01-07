<?php

namespace App\Entity;

use App\Repository\DebtRepository;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=DebtRepository::class)
 */
class Debt implements TransactionPartInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="float")
     */
    private $amount;

    /**
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @ORM\Column(type="boolean")
     */
    private $paid;

    /**
     * @ORM\ManyToOne(targetEntity=Transaction::class, inversedBy="debts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $transaction;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="debts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $owner;

    /**
     * @ORM\OneToMany(targetEntity=Exchange::class, mappedBy="debt")
     */
    private $exchanges;

    /**
     * @ORM\Column(type="datetime")
     */
    private $edited;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $state;

    /**
     * @ORM\Column(type="float")
     */
    private $initialAmount;

    public function __construct()
    {
        $this->exchanges = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getAmount(): ?float
    {
        return $this->amount;
    }

    public function setAmount(float $amount): TransactionPartInterface
    {
        $this->amount = $amount;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function setCreated(\DateTimeInterface $created): TransactionPartInterface
    {
        $this->created = $created;

        return $this;
    }

    public function getPaid(): ?bool
    {
        return $this->paid;
    }

    public function setPaid(bool $paid): TransactionPartInterface
    {
        $this->paid = $paid;

        return $this;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(?Transaction $transaction): TransactionPartInterface
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getOwner(): ?User
    {
        return $this->owner;
    }

    public function setOwner(?User $owner): TransactionPartInterface
    {
        $this->owner = $owner;

        return $this;
    }

    public function getEdited(): ?DateTimeInterface
    {
        return $this->edited;
    }

    public function setEdited(DateTimeInterface $edited): TransactionPartInterface
    {
        $this->edited = $edited;
        return $this;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): TransactionPartInterface
    {
        $this->state = $state;
        return $this;
    }

    /**
     * isLoan
     *
     * @return bool
     */
    public function isLoan(): bool
    {
        return false;
    }

    /**
     * isDebt
     *
     * @return bool
     */
    public function isDebt(): bool
    {
        return true;
    }

    /**
     * @return Collection|Exchange[]
     */
    public function getExchanges(): Collection
    {
        return $this->exchanges;
    }

    public function addExchange(Exchange $exchange): self
    {
        if (!$this->exchanges->contains($exchange)) {
            $this->exchanges[] = $exchange;
            $exchange->setDebt($this);
        }

        return $this;
    }

    public function removeExchange(Exchange $exchange): self
    {
        if ($this->exchanges->removeElement($exchange)) {
            // set the owning side to null (unless already changed)
            if ($exchange->getDebt() === $this) {
                $exchange->setDebt(null);
            }
        }

        return $this;
    }

    /**
     * __toString
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->getTransaction()->getReason().' - '.$this->getAmount();
    }

    public function getInitialAmount(): ?float
    {
        return $this->initialAmount;
    }

    public function setInitialAmount(float $initialAmount): self
    {
        $this->initialAmount = $initialAmount;

        return $this;
    }
}
