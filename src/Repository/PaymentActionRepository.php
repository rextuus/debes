<?php

namespace App\Repository;

use App\Entity\PaymentAction;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method PaymentAction|null find($id, $lockMode = null, $lockVersion = null)
 * @method PaymentAction|null findOneBy(array $criteria, array $orderBy = null)
 * @method PaymentAction[]    findAll()
 * @method PaymentAction[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentActionRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PaymentAction::class);
    }

    // /**
    //  * @return PaymentAction[] Returns an array of PaymentAction objects
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
    public function findOneBySomeField($value): ?PaymentAction
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
    public function persist(PaymentAction $paymentAction): void
    {
        $this->_em->persist($paymentAction);
        $this->_em->flush();
    }
}
