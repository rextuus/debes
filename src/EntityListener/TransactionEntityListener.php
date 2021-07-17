<?php

namespace App\EntityListener;

use App\Entity\Transaction;
use DateTime;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Symfony\Component\String\Slugger\SluggerInterface;

/**
 * TransactionEntityListener
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class TransactionEntityListener
{
    /**
     * @var SluggerInterface
     */
    private $slugger;

    /**
     * TransactionEntityListener constructor.
     *
     * @param SluggerInterface   $slugger
     */
    public function __construct(SluggerInterface $slugger)
    {
        $this->slugger = $slugger;
    }

    /**
     * prePersist
     *
     * @param Transaction        $transaction
     * @param LifecycleEventArgs $event
     *
     * @return void
     */
    public function prePersist(Transaction $transaction, LifecycleEventArgs $event): void
    {
        $slug = $this->computeSlug($transaction);
        if ($slug) {
            $transaction->setSlug($slug);
        }
    }

    /**
     * computeSlug
     *
     * @param Transaction $transaction
     *
     * @return string|null
     */
    private function computeSlug(Transaction $transaction): ?string
    {
        $timeStamp = (new DateTime())->getTimestamp()+rand();
        if (!$transaction->getSlug() || '-' === $transaction->getSlug()) {
            return $this->slugger->slug(substr((string)$transaction, 0, 16))->lower() . $timeStamp;
        }
        return null;
    }

    /**
     * preUpdate
     *
     * @param Transaction        $transaction
     * @param LifecycleEventArgs $event
     *
     * @return void
     */
    public function preUpdate(Transaction $transaction, LifecycleEventArgs $event): void
    {
        $slug = $this->computeSlug($transaction);
        if ($slug) {
            $transaction->setSlug($slug);
        }
    }
}