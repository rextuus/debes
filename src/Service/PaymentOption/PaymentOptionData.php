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
}