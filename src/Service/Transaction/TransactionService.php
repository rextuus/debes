<?php

namespace App\Service\Transaction;

use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\TransactionRepository;
use App\Service\Debt\DebtCreateData;
use App\Service\Debt\DebtDto;
use App\Service\Debt\DebtService;
use App\Service\Loan\LoanCreateData;
use App\Service\Loan\LoanDto;
use App\Service\Loan\LoanService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class TransactionService
{
    const DEBTOR_VIEW = 'debtor';
    const LOANER_VIEW = 'loaner';

    /**
     * @var TransactionFactory
     */
    private $transactionFactory;

    /**
     * @var TransactionRepository
     */
    private $transactionRepository;

    /**
     * @var DebtService
     */
    private $debtService;

    /**
     * @var LoanService
     */
    private $loanService;

    /**
     * TransactionService constructor.
     *
     * @param TransactionFactory    $transactionFactory
     * @param TransactionRepository $transactionRepository
     * @param DebtService           $debtService
     * @param LoanService           $loanService
     */
    public function __construct(
        TransactionFactory $transactionFactory,
        TransactionRepository $transactionRepository,
        DebtService $debtService,
        LoanService $loanService
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->transactionRepository = $transactionRepository;
        $this->debtService = $debtService;
        $this->loanService = $loanService;
    }

    /**
     * storeTransaction
     *
     * @param TransactionData $transactionData
     *
     * @return Transaction
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function storeTransaction(TransactionData $transactionData): Transaction
    {
        $transaction = $this->transactionFactory->createByData($transactionData);

        $this->transactionRepository->persist($transaction);

        return $transaction;
    }

    /**
     * update
     *
     * @param Transaction           $transaction
     * @param TransactionUpdateData $data
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(Transaction $transaction, TransactionUpdateData $data): void
    {
        $this->transactionFactory->mapData($transaction, $data);

        $this->transactionRepository->persist($transaction);
    }

    /**
     * storeSimpleTransaction
     *
     * @param TransactionData $data
     * @param User            $requester
     *
     * @return Transaction
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function storeSimpleTransaction(TransactionData $data, User $requester): Transaction
    {
        $data->setState(Transaction::STATE_READY);
        $transaction = $this->storeTransaction($data);

        $debtData = (new DebtCreateData())->initFromData($data);
        $debtData->setTransaction($transaction);
        $this->debtService->storeDebt($debtData);

        $loanData = (new LoanCreateData())->initFromData($data, $requester);
        $loanData->setTransaction($transaction);
        $this->loanService->storeLoan($loanData);

        return $transaction;
    }

    /**
     * getAllTransactionBelongingUser
     *
     * @param User $owner
     *
     * @return array
     */
    public function getAllTransactionBelongingUser(User $owner): array
    {
        $dtos = array();
        $debtTransactions = $this->debtService->getAllDebtTransactionsForUser($owner);
        foreach ($debtTransactions as $transaction) {
            $dtos[] = TransactionDto::create($transaction, true);
        }
        $loanTransactions = $this->loanService->getAllLoanTransactionsForUser($owner);
        foreach ($loanTransactions as $transaction) {
            $dtos[] = TransactionDto::create($transaction, false);
        }
        return $dtos;
    }

    /**
     * getTotalDebtsForUser
     *
     * @param User $owner
     *
     * @return float
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getTotalDebtsForUser(User $owner): float
    {
        return $this->debtService->getTotalDebtsForUser($owner);
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
        return $this->loanService->getTotalLoansForUser($owner);
    }

    /**
     * getAllDebtTransactionsForUserAndState
     *
     * @param User   $owner
     * @param string $state
     *
     * @return array
     */
    public function getAllDebtTransactionsForUserAndState(User $owner, string $state): array
    {
        $dtos = array();
        $debtTransactions = $this->debtService->getAllDebtTransactionsForUserAndSate($owner, $state);
        foreach ($debtTransactions as $transaction) {
            $dtos[] = DebtDto::create($transaction);
        }
        return $dtos;
    }

    /**
     * getAllLoanTransactionsForUserAndState
     *
     * @param User   $owner
     * @param string $state
     *
     * @return array
     */
    public function getAllLoanTransactionsForUserAndState(User $owner, string $state): array
    {
        $dtos = array();
        $loanTransactions = $this->loanService->getAllDebtTransactionsForUserAndSate($owner, $state);
        foreach ($loanTransactions as $transaction) {
            $dtos[] = LoanDto::create($transaction);
        }
        return $dtos;
    }

    /**
     * acceptDebt
     *
     * @param Transaction $transaction
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function acceptTransaction(Transaction $transaction): void
    {
        $transactionData = (new TransactionUpdateData())->initFrom($transaction);
        $transactionData->setState(Transaction::STATE_ACCEPTED);
        $this->update($transaction, $transactionData);
    }

    /**
     * checkRequestForVariant
     *
     * @param User        $requester
     * @param Transaction $transaction
     * @param string      $variant
     * @param string      $state
     *
     * @return bool
     * @throws Exception
     */
    public function checkRequestForVariant(User $requester, Transaction $transaction, string $variant, string $state): bool
    {
        if ($transaction->getState() !== $state){
            throw new Exception('Transaction is not in correct sate');
        }

        if ($variant === self::DEBTOR_VIEW) {
            if ($requester !== $transaction->getDebts()[0]->getOwner()) {
                throw new Exception('User is not the debtor of this transaction');
            }
            return true;
        } elseif ($variant === self::LOANER_VIEW) {
            if ($requester !== $transaction->getLoans()[0]->getOwner()) {
                throw new Exception('User is not the loaner of this transaction');
            }
            return false;
        } else {
            throw new Exception('User is not involved in this transaction');
        }
    }
}
