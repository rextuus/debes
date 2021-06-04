<?php

namespace App\Repository;

use App\Entity\PaypalAccount;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PaypalAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaypalAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaypalAccount[]    findAll()
 * @method PaypalAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaypalAccountRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaypalAccount::class);
    }

    /**
     * persist
     *
     * @param PaypalAccount $paypalAccount
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function persist(PaypalAccount $paypalAccount): void
    {
        $this->_em->persist($paypalAccount);
        $this->_em->flush();
    }

    /**
     * getPaymentOptionCountForUser
     *
     * @param User $owner
     *
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getPaypalAccountCountForUser(User $owner): int
    {
        $qb = $this->createQueryBuilder('p')
            ->select('count(p.id)')
            ->where('p.owner = :owner')
            ->setParameter('owner', $owner);
        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
