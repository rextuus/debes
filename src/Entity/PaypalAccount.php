<?php

namespace App\Entity;

use App\Repository\PaypalAccountRepository;
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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }
}
