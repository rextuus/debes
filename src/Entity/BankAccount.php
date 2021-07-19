<?php

namespace App\Entity;

use App\Repository\BankAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=BankAccountRepository::class)
 */
class BankAccount extends PaymentOption
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $iban;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $bic;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $accountName;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $bankName;

    /**
     * @ORM\OneToMany(targetEntity=PaymentAction::class, mappedBy="bankAccountSender")
     */
    private $paymentActions;

    public function __construct()
    {
        $this->paymentActions = new ArrayCollection();
    }

    public function getIban(): ?string
    {
        return $this->iban;
    }

    public function setIban(string $iban): self
    {
        $this->iban = $iban;

        return $this;
    }

    public function getBic(): ?string
    {
        return $this->bic;
    }

    public function setBic(string $bic): self
    {
        $this->bic = $bic;

        return $this;
    }

    public function getAccountName(): ?string
    {
        return $this->accountName;
    }

    public function setAccountName(string $accountName): self
    {
        $this->accountName = $accountName;

        return $this;
    }

    public function getBankName(): ?string
    {
        return $this->bankName;
    }

    public function setBankName(string $bankName): self
    {
        $this->bankName = $bankName;

        return $this;
    }

    /**
     * @return Collection|PaymentAction[]
     */
    public function getPaymentActions(): Collection
    {
        return $this->paymentActions;
    }

    public function addPaymentAction(PaymentAction $paymentAction): self
    {
        if (!$this->paymentActions->contains($paymentAction)) {
            $this->paymentActions[] = $paymentAction;
            $paymentAction->setBankAccountSender($this);
        }

        return $this;
    }

    public function removePaymentAction(PaymentAction $paymentAction): self
    {
        if ($this->paymentActions->removeElement($paymentAction)) {
            // set the owning side to null (unless already changed)
            if ($paymentAction->getBankAccountSender() === $this) {
                $paymentAction->setBankAccountSender(null);
            }
        }

        return $this;
    }
}
