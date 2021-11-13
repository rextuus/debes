<?php

namespace App\Entity;

use App\Repository\PaymentActionRepository;
use Doctrine\ORM\Mapping as ORM;
use http\Exception;

/**
 * @ORM\Entity(repositoryClass=PaymentActionRepository::class)
 */
class PaymentAction
{
    const VARIANT_BANK = 'bank';
    const VARIANT_PAYPAL = 'paypal';
    const VARIANT_EXCHANGE = 'exchange';

    const ALLOWED_VARIANTS = [
        self::VARIANT_BANK,
        self::VARIANT_PAYPAL,
        self::VARIANT_EXCHANGE,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=Transaction::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $transaction;

    /**
     * @ORM\OneToOne(targetEntity=Exchange::class, cascade={"persist", "remove"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $exchange;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $variant;

    /**
     * @ORM\ManyToOne(targetEntity=BankAccount::class, inversedBy="paymentActions")
     */
    private $bankAccountSender;

    /**
     * @ORM\ManyToOne(targetEntity=BankAccount::class, inversedBy="paymentActions")
     */
    private $bankAccountReceiver;

    /**
     * @ORM\ManyToOne(targetEntity=PaypalAccount::class, inversedBy="paymentActions")
     */
    private $paypalAccountSender;

    /**
     * @ORM\ManyToOne(targetEntity=PaypalAccount::class, inversedBy="paymentActions")
     */
    private $paypalAccountReceiver;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTransaction(): ?Transaction
    {
        return $this->transaction;
    }

    public function setTransaction(Transaction $transaction): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    public function getExchange(): ?Exchange
    {
        return $this->exchange;
    }

    public function setExchange(?Exchange $exchange): self
    {
        $this->exchange = $exchange;

        return $this;
    }

    public function getVariant(): ?string
    {
        return $this->variant;
    }

    /**
     * @throws \Exception
     */
    public function setVariant(string $variant): self
    {
        if (!in_array($variant, self::ALLOWED_VARIANTS)){
            throw new \Exception('Invalid variant');
        }

        $this->variant = $variant;

        return $this;
    }

    public function getBankAccountSender(): ?BankAccount
    {
        return $this->bankAccountSender;
    }

    public function setBankAccountSender(?BankAccount $bankAccountSender): self
    {
        $this->bankAccountSender = $bankAccountSender;

        return $this;
    }

    public function getBankAccountReceiver(): ?BankAccount
    {
        return $this->bankAccountReceiver;
    }

    public function setBankAccountReceiver(?BankAccount $bankAccountReceiver): self
    {
        $this->bankAccountReceiver = $bankAccountReceiver;

        return $this;
    }

    public function getPaypalAccountSender(): ?PaypalAccount
    {
        return $this->paypalAccountSender;
    }

    public function setPaypalAccountSender(?PaypalAccount $paypalAccountSender): self
    {
        $this->paypalAccountSender = $paypalAccountSender;

        return $this;
    }

    public function getPaypalAccountReceiver(): ?PaypalAccount
    {
        return $this->paypalAccountReceiver;
    }

    public function setPaypalAccountReceiver(?PaypalAccount $paypalAccountReceiver): self
    {
        $this->paypalAccountReceiver = $paypalAccountReceiver;

        return $this;
    }
}
