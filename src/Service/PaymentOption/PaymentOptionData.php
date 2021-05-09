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
     * @boolean
     */
    private $enabled;

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
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled): void
    {
        $this->enabled = $enabled;
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
