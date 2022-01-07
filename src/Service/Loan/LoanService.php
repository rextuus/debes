<?php

namespace App\Service\Loan;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
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
     * @param float  $amount
     *
     * @return array
     */
    public function getAllLoanTransactionsForUserAndSate(User $owner, string $state, float $amount): array
    {
        return $this->loanRepository->getAllLoanTransactionsForUserAndSate($owner, $state, $amount);
    }

    /**
     * getAllDebtTransactionsForUserAndSate
     *
     * @param Debt $debt
     *
     * @return array
     */
    public function getAllExchangeLoansForDebt(Debt $debt): array
    {
        return $this->loanRepository->getAllExchangeLoansForDebt(
            $debt->getOwner(),
            Transaction::STATE_ACCEPTED,
            $debt->getAmount(),
            $debt->getTransaction()->getLoanerIds()
        );
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
