<?php

namespace App\Service\PaymentOption;

use App\Entity\User;

abstract class PaymentOptionData
{

    /**
     * @var User
     */
    private $owner;

    /**
     * @var boolean
     */
    private $enabled;

    /**
     * @var boolean
     */
    private $preferred;

    /**
     * @var string
     */
    private $description;

    /**
     * @return User
     */
    public function getOwner(): User
    {
        return $this->owner;
    }

    /**
     * @param User $owner
     */
    public function setOwner(User $owner): void
    {
        $this->owner = $owner;
    }

    /**
     * @return bool
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     */
    public function setEnabled(bool $enabled): void
    {
        $this->enabled = $enabled;
    }

    /**
     * @return bool
     */
    public function isPreferred(): bool
    {
        return $this->preferred;
    }

    /**
     * @param bool $preferred
     */
    public function setPreferred(bool $preferred): void
    {
        $this->preferred = $preferred;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * initFromUser
     *
     * @param User $owner
     *
     * @return $this
     */
    public function initFromUser(User $owner): self
    {
        $this->setOwner($owner);

        return $this;
    }
}
