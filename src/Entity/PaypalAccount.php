<?php

namespace App\Entity;

use App\Repository\PaypalAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PaypalAccountRepository::class)
 */
class PaypalAccount extends PaymentOption
{
    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity=PaymentAction::class, mappedBy="paypalAccountSender")
     */
    private $paymentActions;

    public function __construct()
    {
        $this->paymentActions = new ArrayCollection();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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
            $paymentAction->setPaypalAccountSender($this);
        }

        return $this;
    }

    public function removePaymentAction(PaymentAction $paymentAction): self
    {
        if ($this->paymentActions->removeElement($paymentAction)) {
            // set the owning side to null (unless already changed)
            if ($paymentAction->getPaypalAccountSender() === $this) {
                $paymentAction->setPaypalAccountSender(null);
            }
        }

        return $this;
    }
}
