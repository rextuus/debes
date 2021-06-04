<?php

namespace App\Repository;

use App\Entity\BankAccount;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method BankAccount|null find($id, $lockMode = null, $lockVersion = null)
 * @method BankAccount|null findOneBy(array $criteria, array $orderBy = null)
 * @method BankAccount[]    findAll()
 * @method BankAccount[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BankAccountRepository extends ServiceEntityRepository
{

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BankAccount::class);
    }

    /**
     * persist
     *
     * @param BankAccount $bankAccount
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function persist(BankAccount $bankAccount): void
    {
        $this->_em->persist($bankAccount);
        $this->_em->flush();
    }

    /**
     * getPaypalAccountCountForUser
     *
     * @param User $owner
     *
     * @return int
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getPaypalAccountCountForUser(User $owner): int
    {
        $qb = $this->createQueryBuilder('b')
            ->select('count(b.id)')
            ->where('b.owner = :owner')
            ->setParameter('owner', $owner);
        return (int)$qb->getQuery()->getSingleScalarResult();
    }
}
