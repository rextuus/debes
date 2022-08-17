<?php

namespace App\Service\Transaction;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Entity\User;
use App\Repository\TransactionRepository;
use App\Service\Debt\DebtCreateData;
use App\Service\Debt\DebtData;
use App\Service\Debt\DebtService;
use App\Service\Debt\DebtUpdateData;
use App\Service\Loan\LoanCreateData;
use App\Service\Loan\LoanDto;
use App\Service\Loan\LoanService;
use App\Service\Loan\LoanUpdateData;
use DateTime;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var DtoProvider
     */
    private $dtoProvider;

    /**
     * @var TransactionProcessor
     */
    private $transactionProcessor;

    /**
     * TransactionService constructor.
     *
     * @param TransactionFactory     $transactionFactory
     * @param TransactionRepository  $transactionRepository
     * @param DebtService            $debtService
     * @param LoanService            $loanService
     * @param EntityManagerInterface $entityManager
     * @param DtoProvider            $dtoProvider
     */
    public function __construct(
        TransactionFactory $transactionFactory,
        TransactionRepository $transactionRepository,
        DebtService $debtService,
        LoanService $loanService,
        EntityManagerInterface $entityManager,
        DtoProvider $dtoProvider
    ) {
        $this->transactionFactory = $transactionFactory;
        $this->transactionRepository = $transactionRepository;
        $this->debtService = $debtService;
        $this->loanService = $loanService;
        $this->entityManager = $entityManager;
        $this->dtoProvider = $dtoProvider;
    }

    /**
     * storeTransaction
     *
     * @param TransactionData $transactionData
     * @param bool            $persist
     *
     * @return Transaction
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function storeTransaction(TransactionData $transactionData, bool $persist = true): Transaction
    {
        $transaction = $this->transactionFactory->createByData($transactionData);

        if ($persist) {
            $this->transactionRepository->persist($transaction);
        }

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
    public function update(Transaction $transaction, TransactionUpdateData $data = null): Transaction
    {
        if (!is_null($data)){
            $this->transactionFactory->mapData($transaction, $data);
        }
        $this->transactionRepository->persist($transaction);
        $this->entityManager->flush();
        return $transaction;
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
    public function storeSingleTransaction(TransactionData $data, User $requester): Transaction
    {
        $data->setState(Transaction::STATE_READY);
        $transaction = $this->storeTransaction($data);

        $debtData = (new DebtCreateData())->initFromData($data);
        $debtData->setTransaction($transaction);
        $debt = $this->debtService->storeDebt($debtData);

        $loanData = (new LoanCreateData())->initFromData($data, $requester);
        $loanData->setTransaction($transaction);
        $loan = $this->loanService->storeLoan($loanData);

        $transaction->addDebt($debt);
        $transaction->addLoan($loan);
        $this->update($transaction);
        return $transaction;
    }

    /**
     * storeSimpleTransaction
     *
     * @param TransactionCreateMultipleData $data
     *
     * @return Transaction
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function storeMultipleTransaction(TransactionCreateMultipleData $data): Transaction
    {
        $transactionData = new TransactionCreateData();
        $transactionData->setReason($data->getReason());
        $transactionData->setAmount($data->getCompleteAmount());
        $transactionData->setState(Transaction::STATE_READY);

        $transaction = $this->storeTransaction($transactionData);

        foreach ($data->getDebtorsData() as $debtData) {
            $debtData->setTransaction($transaction);
            $debtData->setPaid(false);
            $debtData->setCreated(new DateTime());
            $debtData->setReason($data->getReason());
            $debtData->setState(Transaction::STATE_READY);
            $debt = $this->debtService->storeDebt($debtData);
            $transaction->addDebt($debt);
        }

        foreach ($data->getLoanersData() as $loanData) {
            $loanData->setTransaction($transaction);
            $loanData->setPaid(false);
            $loanData->setCreated(new DateTime());
            $loanData->setReason($data->getReason());
            $loanData->setState(Transaction::STATE_READY);
            $loan = $this->loanService->storeLoan($loanData);
            $transaction->addLoan($loan);
        }

        $transactionUpdateData = (new TransactionUpdateData())->initFrom($transaction);
        $this->update($transaction, $transactionUpdateData);

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
        $dtos = [];
        $debts = $this->debtService->getAllDebtTransactionsForUserAndState($owner, $state);
        foreach ($debts as $debt) {
            $dtos[] = $this->dtoProvider->createDebtDto($debt);
        }
        return $dtos;
    }

    /**
     * createDtoFromTransaction
     *
     * @param Transaction $transaction
     * @param bool        $isDebtVariant
     *
     * @return TransactionDtos\TransactionDto
     */
    public function createDtoFromTransaction(
        Transaction $transaction,
        bool $isDebtVariant
    ): TransactionDtos\TransactionDto {
        return $this->dtoProvider->createTransactionDto($transaction, $isDebtVariant);
    }

    /**
     * getAllLoanTransactionsForUserAndState
     *
     * @param User   $owner
     * @param string $state
     *
     * @return LoanDto[]
     */
    public function getAllLoanTransactionPartsForUserAndStateDtoVariant(User $owner, string $state): array
    {
        $dtos = array();
        $loans = $this->loanService->getAllLoanTransactionsForUserAndSate($owner, $state, 0.0);
        foreach ($loans as $loan) {
            $dtos[] = $this->dtoProvider->createLoanDto($loan);
        }
        return $dtos;
    }

    /**
     * getAllLoanTransactionsForUserAndState
     *
     * @param User   $owner
     * @param string $state
     *
     * @return Loan[]
     */
    public function getAllLoanTransactionsForUserAndState(User $owner, string $state): array
    {
        return $this->loanService->getAllLoanTransactionsForUserAndSate($owner, $state, 0.0);
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
    public function confirmTransaction(Transaction $transaction): void
    {
        $transactionData = (new TransactionUpdateData())->initFrom($transaction);
        $transactionData->setState(Transaction::STATE_CONFIRMED);
        $this->update($transaction, $transactionData);
    }

    /**
     * declineTransaction
     *
     * @param Debt $debt
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function declineDebt(Debt $debt): void
    {
        $debtData = (new DebtUpdateData())->initFrom($debt);
        $debtData->setState(Transaction::STATE_DECLINED);
        $this->debtService->update($debt, $debtData);
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
    public function checkRequestForVariant(
        User $requester,
        Transaction $transaction,
        string $variant,
        string $state
    ): bool {
        if ($variant === self::DEBTOR_VIEW) {
            $debt = $this->getDebtPartOfUserForTransaction($transaction, $requester);
            if (is_null($debt)) {
                throw new Exception('User is not a loaner of this transaction');
            }
            if ($debt->getState() !== $state) {
                throw new Exception('TransactionPart is not in correct sate');
            }
            return true;
        } elseif ($variant === self::LOANER_VIEW) {
            $loan = $this->getLoanPartOfUserForTransaction($transaction, $requester);
            if (is_null($loan)) {
                throw new Exception('User is not a loaner of this transaction');
            }
            if ($loan->getState() !== $state) {
                dump($state);
                dump($loan->getState());
                throw new Exception('TransactionPart is not in correct sate');
            }
            return false;
        } else {
            throw new Exception('User is not involved in this transaction');
        }
    }

    /**
     * getTransactionBySlug
     *
     * @param string $slug
     *
     * @return Transaction|null
     */
    public function getTransactionBySlug(string $slug): ?Transaction
    {
        return $this->transactionRepository->findOneBy(['slug' => $slug]);
    }

    public function updateInclusive(?Transaction $transaction, TransactionUpdateData $transactionUpdateData)
    {
        $this->update($transaction, $transactionUpdateData);

        $loan = $transaction->getLoans()[0];
        $loanData = (new LoanUpdateData())->initFrom($loan);
        $loanData->setAmount($transaction->getAmount());
        $loanData->setReason($transaction->getReason());
        $loanData->setState($transaction->getState());
        $this->loanService->update($loan, $loanData);

        $debt = $transaction->getDebts()[0];
        $debtData = (new DebtUpdateData())->initFrom($debt);
        $debtData->setAmount($transaction->getAmount());
        $debtData->setReason($transaction->getReason());
        $debtData->setState($transaction->getState());
        $this->debtService->update($debt, $debtData);
    }

    public function updateInclusiveMulti(Transaction $transaction, TransactionUpdateData $transactionUpdateData)
    {
        $transaction = $this->update($transaction, $transactionUpdateData);
        dump($transaction);
        foreach ($transaction->getLoans() as $loan){
            dump($loan->getId());
            $loanData = (new LoanUpdateData())->initFrom($loan);
            $loanData->setAmount($transaction->getAmount());
            $loanData->setReason($transaction->getReason());
            $loanData->setState($transactionUpdateData->getState());
            $this->loanService->update($loan, $loanData);
        }
        foreach ($transaction->getDebts() as $debt){
            $debtData = (new DebtUpdateData())->initFrom($debt);
            $debtData->setAmount($transaction->getAmount());
            $debtData->setReason($transaction->getReason());
            $debtData->setState($transactionUpdateData->getState());
            $this->debtService->update($debt, $debtData);
        }
    }

    /**
     * getDebtPartOfUserForTransaction
     *
     * @param Transaction $transaction
     * @param User        $user
     *
     * @return Debt|null
     */
    public function getDebtPartOfUserForTransaction(Transaction $transaction, User $user): ?Debt
    {
        foreach ($transaction->getDebts() as $debt) {
            if ($debt->getOwner() === $user) {
                return $debt;
            }
        }
        return null;
    }

    /**
     * getLoanPartOfUserForTransaction
     *
     * @param Transaction $transaction
     * @param User        $user
     *
     * @return Loan|null
     */
    public function getLoanPartOfUserForTransaction(Transaction $transaction, User $user): ?Loan
    {
        foreach ($transaction->getLoans() as $loan) {
            if ($loan->getOwner() === $user) {
                return $loan;
            }
        }
        return null;
    }

    public function getTransactionById(int $int)
    {
        return $this->transactionRepository->find($int);
    }

    /**
     * @return Transaction[]
     */
    public function getAll(): array
    {
        return $this->transactionRepository->findAll();
    }


}
