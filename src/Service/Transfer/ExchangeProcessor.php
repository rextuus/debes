<?php

namespace App\Service\Transfer;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Service\Debt\DebtUpdateData;
use App\Service\Exchange\ExchangeCreateData;
use App\Service\Exchange\ExchangeService;
use App\Service\Loan\LoanService;
use App\Service\Loan\LoanUpdateData;
use App\Service\Transaction\TransactionService;
use App\Service\Transaction\TransactionUpdateData;
use DateTime;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * ExchangeProcessor
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class ExchangeProcessor
{
    /**
     * @var TransactionService
     */
    private $transactionService;

    /**
     * @var ExchangeService
     */
    private $exchangeService;

    /**
     * @var LoanService
     */
    private $loanService;

    /**
     * ExchangeProcessor constructor.
     */
    public function __construct(
        TransactionService $transactionService,
        ExchangeService $exchangeService,
        LoanService $loanService
    ) {
        $this->transactionService = $transactionService;
        $this->exchangeService = $exchangeService;
        $this->loanService = $loanService;
    }

    /**
     * findExchangeCandidatesForTransaction
     *
     * @param Debt $debt
     *
     * @return ExchangeCandidateSet
     */
    public function findExchangeCandidatesForTransaction(Debt $debt): ExchangeCandidateSet
    {
        // get all loans from given user
        $candidates = $this->loanService->getAllExchangeLoansForDebt($debt);
        dump($candidates);
//        $fittingCandidates = array();
        $nonFittingCandidates = array();
//        foreach ($candidates as $candidate) {
//            /** @var Transaction $candidate */
//            if ($candidate->getAmount() >= $debt->getAmount()) {
//                $fittingCandidates[] = $candidate;
//            } else {
//                $nonFittingCandidates[] = $candidate;
//            }
//        }

        $exchangeCandidateSet = new ExchangeCandidateSet();;
        $exchangeCandidateSet->setFittingCandidates($candidates);
        $exchangeCandidateSet->setNonFittingCandidates($nonFittingCandidates);

        return $exchangeCandidateSet;
    }

    /**
     * calculateExchange
     *
     * @param string $slug1
     * @param string $slug2
     *
     * @return ExchangeDto
     */
    public function calculateExchange(string $slug1, string $slug2): ExchangeDto
    {
        $transaction = $this->transactionService->getTransactionBySlug($slug1);
        $transactionToExchange = $this->transactionService->getTransactionBySlug($slug2);
        $exchangeDto = (new ExchangeDto())->initFromTransactions($transaction, $transactionToExchange);
        $difference = $transaction->getAmount() - $transactionToExchange->getAmount();
        $exchangeDto->setDifference($difference);

        return $exchangeDto;
    }

    /**
     * exchangeDebtAndLoan
     *
     * @param Debt $debt
     * @param Loan $loan
     *
     * @return void
     */
    public function exchangeDebtAndLoan(Debt $debt, Loan $loan): void
    {
        // update debt and transaction

        // debt is greater than loan => set loan to 0 and debt to difference
        if ($debt->getAmount() > $loan->getAmount()){
            $difference = $debt->getAmount() - $loan->getAmount();
            $transactionDifference = $difference;
            $debtUpdateData = new DebtUpdateData();
            $debtUpdateData->setAmount($difference);
            $debtUpdateData->setState(Transaction::STATE_PARTIAL_CLEARED);

            $loanUpdateData = new LoanUpdateData();
            $loanUpdateData->setAmount(0.0);
            $loanUpdateData->setPaid(true);
            $loanUpdateData->setState(Transaction::STATE_CLEARED);

        }else{
            $difference = $loan->getAmount() - $debt->getAmount();
            $transactionDifference = $difference;
            $debtUpdateData = new DebtUpdateData();
            $debtUpdateData->setAmount(0.0);
            $debtUpdateData->setState(Transaction::STATE_CLEARED);
            $debtUpdateData->setPaid(true);

            $loanUpdateData = new LoanUpdateData();
            $loanUpdateData->setAmount($difference);
            $loanUpdateData->setState(Transaction::STATE_PARTIAL_CLEARED);
        }
        $debtUpdateData->setEdited(new DateTime());
        $loanUpdateData->setEdited(new DateTime());


        // create exchange
    }

    /**
     * exchangeTransactions
     *
     * @param string $transactionSlug
     * @param string $transactionToExchangeSlug
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function exchangeTransactions(string $transactionSlug, string $transactionToExchangeSlug): void
    {
        $transaction = $this->transactionService->getTransactionBySlug($transactionSlug);
        $transactionToExchange = $this->transactionService->getTransactionBySlug($transactionToExchangeSlug);

        $transactionUpdateData = (new TransactionUpdateData())->initFrom($transaction);
        $exchangeTransactionUpdateData = (new TransactionUpdateData())->initFrom($transactionToExchange);

        $exchangeCreationDataForTransaction = new ExchangeCreateData();
        $exchangeCreationDataForExchangeTransaction = new ExchangeCreateData();

        if ($transaction->getAmount() >= $transactionToExchange->getAmount()) {
            $difference = $transaction->getAmount() - $transactionToExchange->getAmount();
            $transactionUpdateData->setAmount($difference);
            $exchangeTransactionUpdateData->setState(Transaction::STATE_ACCEPTED);

            $exchangeCreationDataForTransaction->setRemainingAmount($difference);
            $exchangeCreationDataForExchangeTransaction->setRemainingAmount(0);

            $this->transactionService->updateInclusive($transaction, $transactionUpdateData);
            $this->transactionService->update($transactionToExchange, $exchangeTransactionUpdateData);
        } else {
            $difference = $transactionToExchange->getAmount() - $transaction->getAmount();
            $transactionUpdateData->setState(Transaction::STATE_CONFIRMED);
            $exchangeTransactionUpdateData->setAmount($difference);

            $exchangeCreationDataForExchangeTransaction->setRemainingAmount($difference);
            $exchangeCreationDataForTransaction->setRemainingAmount(0);

            $this->transactionService->update($transaction, $transactionUpdateData);
            $this->transactionService->updateInclusive($transactionToExchange, $exchangeTransactionUpdateData);
        }

        // create an single exchange for each transaction
        $exchangeCreationDataForTransaction->setTransaction($transaction);
        $exchangeCreationDataForTransaction->setAmount($transactionToExchange->getAmount());
        $exchangeCreationDataForExchangeTransaction->setTransaction($transactionToExchange);
        $exchangeCreationDataForExchangeTransaction->setAmount($transactionToExchange->getAmount());

        $this->exchangeService->storeExchange($exchangeCreationDataForTransaction);
        $this->exchangeService->storeExchange($exchangeCreationDataForExchangeTransaction);
    }
}
