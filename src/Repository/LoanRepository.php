<?php

namespace App\Repository;

use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Loan|null find($id, $lockMode = null, $lockVersion = null)
 * @method Loan|null findOneBy(array $criteria, array $orderBy = null)
 * @method Loan[]    findAll()
 * @method Loan[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class LoanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loan::class);
    }

    /**
     * persist
     *
     * @param Loan $loan
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function persist(Loan $loan): void
    {
        $this->_em->persist($loan);
        $this->_em->flush();
    }

    /**
     * findTransactionsForUser
     * @param User $owner
     *
     * @return int|mixed|string
     */
    public function findTransactionsForUser(User $owner)
    {
        return $this->createQueryBuilder('l' )
            ->select('t')
            ->leftJoin(Transaction::class, 't','WITH', 'l.transaction = t.id')
            ->where('l.owner = :owner')
            ->setParameter('owner', $owner)
            ->orderBy('l.amount', 'ASC')
            ->getQuery()->getResult()
            ;
    }

    /**
     * getTotalLoansForUser
     *
     * @param User $owner
     *
     * @return float
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getTotalLoansForUser(User $owner): float
    {
        $qb = $this->createQueryBuilder('l')
            ->select('SUM(l.amount)')
            ->where('l.owner = :owner')
            ->setParameter('owner', $owner);

        return (float)$qb->getQuery()->getSingleScalarResult();
    }

    /**
     * getAllDebtTransactionsForUserAndSate
     * @param User   $owner
     * @param string $state
     *
     * @return int|mixed|string
     */
    public function getAllDebtTransactionsForUserAndSate(User $owner, string $state)
    {
        return $this->createQueryBuilder('l')
            ->select('t')
            ->leftJoin(Transaction::class, 't', 'WITH', 'l.transaction = t.id')
            ->where('l.owner = :owner')
            ->andWhere('t.state = :state')
            ->setParameter('owner', $owner)
            ->setParameter('state', $state)
            ->orderBy('t.created', 'ASC')
            ->getQuery()->getResult();
    }
}
