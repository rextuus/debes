<?php

namespace App\Service\Transaction;

use DateTime;

class TransactionUpdateData extends TransactionData
{
    /**
     * @var DateTime
     */
    private $created;

    /**
     * @var DateTime|null
     */
    private $edited;

    /**
     * @var string
     */
    private $state;

    /**
     * @return DateTime
     */
    public function getCreated(): DateTime
    {
        return $this->created;
    }

    /**
     * @param DateTime $created
     */
    public function setCreated(DateTime $created): void
    {
        $this->created = $created;
    }

    /**
     * @return DateTime|null
     */
    public function getEdited(): ?DateTime
    {
        return $this->edited;
    }

    /**
     * @param DateTime|null $edited
     */
    public function setEdited(?DateTime $edited): void
    {
        $this->edited = $edited;
    }

    /**
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->state;
    }

    /**
     * @param string|null $state
     */
    public function setState(?string $state): void
    {
        $this->state = $state;
    }
}
