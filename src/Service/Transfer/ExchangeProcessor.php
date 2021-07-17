<?php

namespace App\Service\Transfer;

use App\Entity\Transaction;
use App\Service\Exchange\ExchangeCreateData;
use App\Service\Exchange\ExchangeService;
use App\Service\Loan\LoanUpdateData;
use App\Service\Transaction\TransactionService;
use App\Service\Transaction\TransactionUpdateData;
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
     * ExchangeProcessor constructor.
     */
    public function __construct(TransactionService $transactionService, ExchangeService $exchangeService)
    {
        $this->transactionService = $transactionService;
        $this->exchangeService = $exchangeService;
    }

    /**
     * findExchangeCandidatesForTransaction
     *
     * @param Transaction $transaction
     *
     * @return ExchangeCandidateSet
     */
    public function findExchangeCandidatesForTransaction(Transaction $transaction): ExchangeCandidateSet
    {
        $candidates = $this->transactionService->getAllLoanTransactionsForUserAndState(
            $transaction->getDebtor(),
            Transaction::STATE_ACCEPTED
        );
        $fittingCandidates = array();
        $nonFittingCandidates = array();
        foreach ($candidates as $candidate) {
            /** @var Transaction $candidate */
            if ($candidate->getAmount() >= $transaction->getAmount()) {
                $fittingCandidates[] = $candidate;
            } else {
                $nonFittingCandidates[] = $candidate;
            }
        }

        $exchangeCandidateSet = new ExchangeCandidateSet();;
        $exchangeCandidateSet->setFittingCandidates($fittingCandidates);
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
            $exchangeTransactionUpdateData->setState(Transaction::STATE_CLEARED);

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
        $exchangeCreationDataForExchangeTransaction->setTransaction($transactionToExchange);

        $this->exchangeService->storeExchange($exchangeCreationDataForTransaction);
        $this->exchangeService->storeExchange($exchangeCreationDataForExchangeTransaction);
    }
}
