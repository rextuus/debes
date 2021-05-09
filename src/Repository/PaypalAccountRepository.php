<?php

namespace App\Repository;

use App\Entity\PaypalAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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

    // /**
    //  * @return PaypalAccount[] Returns an array of PaypalAccount objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('p.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?PaypalAccount
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
