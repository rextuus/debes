<?php

namespace App\Service\Loan;

use App\Entity\Loan;
use App\Entity\User;
use App\Repository\LoanRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class LoanService
{

    /**
     * @var LoanFactory
     */
    private $loanFactory;

    /**
     * @var LoanRepository
     */
    private $loanRepository;

    /**
     * LoanService constructor.
     *
     * @param LoanFactory    $loanFactory
     * @param LoanRepository $loanRepository
     */
    public function __construct(
        LoanFactory $loanFactory,
        LoanRepository $loanRepository
    ) {
        $this->loanFactory = $loanFactory;
        $this->loanRepository = $loanRepository;
    }

    /**
     * storeLoan
     *
     * @param LoanCreateData $loanData
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function storeLoan(LoanCreateData $loanData): void
    {
        $loan = $this->loanFactory->createByData($loanData);

        $this->loanRepository->persist($loan);
    }

    /**
     * update
     *
     * @param Loan           $loan
     * @param LoanUpdateData $data
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(Loan $loan, LoanUpdateData $data): void
    {
        $this->loanFactory->mapData($loan, $data);

        $this->loanRepository->persist($loan);
    }

    /**
     * getAllLoanTransactionsForUser
     *
     * @param User $user
     *
     * @return array
     */
    public function getAllLoanTransactionsForUser(User $user): array
    {
        return $this->loanRepository->findTransactionsForUser($user);
    }

    /**
     * getAllDebtTransactionsForUserAndSate
     *
     * @param User   $owner
     * @param string $state
     *
     * @return array
     *
     */
    public function getAllLoanTransactionsForUserAndSate(User $owner, string $state): array
    {
        return $this->loanRepository->getAllLoanTransactionsForUserAndSate($owner, $state);
    }

    /**
     * getTotalLoansForUser
     *
     * @param User $user
     *
     * @return float
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getTotalLoansForUser(User $user): float
    {
        return $this->loanRepository->getTotalLoansForUser($user);
    }
}
