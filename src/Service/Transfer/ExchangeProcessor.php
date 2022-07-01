<?php

namespace App\Service\Transfer;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Entity\User;
use App\Service\Debt\DebtService;
use App\Service\Debt\DebtUpdateData;
use App\Service\Exchange\ExchangeCreateData;
use App\Service\Exchange\ExchangeService;
use App\Service\Loan\LoanService;
use App\Service\Loan\LoanUpdateData;
use App\Service\Transaction\TransactionPartDataInterface;
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
     * @var DebtService
     */
    private $debtService;

    /**
     * ExchangeProcessor constructor.
     * @param TransactionService $transactionService
     * @param ExchangeService $exchangeService
     * @param LoanService $loanService
     * @param DebtService $debtService
     */
    public function __construct(
        TransactionService $transactionService,
        ExchangeService $exchangeService,
        LoanService $loanService,
        DebtService $debtService
    ) {
        $this->transactionService = $transactionService;
        $this->exchangeService = $exchangeService;
        $this->loanService = $loanService;
        $this->debtService = $debtService;
    }

    /**
     * findExchangeCandidatesForTransaction
     *
     * @param Debt $debt
     *
     * @return ExchangeCandidateSet
     */
    public function findExchangeCandidatesForTransactionPart(Debt $debt): ExchangeCandidateSet
    {
        // debt comes in => we search for all Loans where debt.owner is owner
        $candidates = $this->transactionService->getAllLoanTransactionPartsForUserAndState(
            $debt->getOwner(),
            Transaction::STATE_ACCEPTED
        );
        $fittingCandidates = array();
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
     * @param TransactionPartInterface $transactionPart1
     * @param TransactionPartInterface $transactionPart2
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function exchangeTransactionParts(TransactionPartInterface $transactionPart1, TransactionPartInterface $transactionPart2): void
    {
        if ($transactionPart1->getAmount() >= $transactionPart2->getAmount()) {
            $this->fillExchangeCreateDataSets($transactionPart1, $transactionPart2);
            $this->fillTransactionUpdateDataSets($transactionPart1, $transactionPart2);
        } else {
            $this->fillExchangeCreateDataSets($transactionPart2, $transactionPart1);
            $this->fillTransactionUpdateDataSets($transactionPart2, $transactionPart1);
        }
    }

    /**
     * fillExchangeCreateDataSets
     *
     * @param TransactionPartInterface $transactionWithHigherAmount
     * @param TransactionPartInterface $transactionWithLowerAmount
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function fillExchangeCreateDataSets(TransactionPartInterface $transactionWithHigherAmount, TransactionPartInterface $transactionWithLowerAmount)
    {
        $exchangeCreationDataHigher = new ExchangeCreateData();
        $exchangeCreationDataHigher->setTransaction($transactionWithHigherAmount->getTransaction());
        $exchangeCreationDataLower = new ExchangeCreateData();
        $exchangeCreationDataLower->setTransaction($transactionWithLowerAmount->getTransaction());

        $exchangeCreationDataHigher->setAmount($transactionWithLowerAmount->getAmount());
        $exchangeCreationDataLower->setAmount($transactionWithLowerAmount->getAmount());

        $exchangeCreationDataHigher->setRemainingAmount($transactionWithHigherAmount->getAmount() - $transactionWithLowerAmount->getAmount());
        $exchangeCreationDataLower->setRemainingAmount(0);

        if ($transactionWithHigherAmount->isDebt()){
            $exchangeCreationDataHigher->setDebt($transactionWithHigherAmount);
            $exchangeCreationDataHigher->setLoan($transactionWithLowerAmount);
            $exchangeCreationDataLower->setDebt($transactionWithHigherAmount);
            $exchangeCreationDataLower->setLoan($transactionWithLowerAmount);
        }else{
            $exchangeCreationDataHigher->setDebt($transactionWithLowerAmount);
            $exchangeCreationDataHigher->setLoan($transactionWithHigherAmount);
            $exchangeCreationDataLower->setDebt($transactionWithLowerAmount);
            $exchangeCreationDataLower->setLoan($transactionWithHigherAmount);
        }

        $this->exchangeService->storeExchange($exchangeCreationDataHigher);
        $this->exchangeService->storeExchange($exchangeCreationDataLower);
    }

/**
     * fillTransactionUpdateDataSets
     *
     * @param TransactionPartInterface $transactionPartWithHigherAmount
     * @param TransactionPartInterface $transactionPartWithLowerAmount
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function fillTransactionUpdateDataSets(TransactionPartInterface $transactionPartWithHigherAmount, TransactionPartInterface $transactionPartWithLowerAmount)
    {
        // we got 4 scenarios:
        // 1. high is single | low is single
        // 2. high is multi  | low is single
        // 3. high is single | low is multi
        // 4. high is multi  | low is multi

        // we need four transactions parts in the end:
        $user1 = null;
        $user2 = null;

        // HIGH = single | LOW = single
        $higherAmountTransaction = $transactionPartWithHigherAmount->getTransaction();
        $lowerAmountTransaction = $transactionPartWithLowerAmount->getTransaction();
        if ($higherAmountTransaction->isSingleTransaction() && $lowerAmountTransaction->isSingleTransaction()){
//            $this->updateSingleTransactions($transactionPartWithHigherAmount->getTransaction(), $transactionPartWithLowerAmount->getTransaction());
            $user1 = $higherAmountTransaction->getDebtor();
            $user2 = $higherAmountTransaction->getLoaner();

            $updateDataCollection = $this->prepareUpdateDataSets($user1, $user2, $higherAmountTransaction, $lowerAmountTransaction);
            $highTransactionData = $updateDataCollection->getTransactionHighData();
            $highTransactionData->setState(Transaction::STATE_ACCEPTED);
            $this->transactionService->updateInclusive($higherAmountTransaction, $highTransactionData);

            $lowTransactionData = $updateDataCollection->getTransactionLowData();
            $lowTransactionData->setState(Transaction::STATE_CLEARED);
            $this->transactionService->updateInclusive($lowerAmountTransaction, $lowTransactionData);
        }
        // HIGH = single | LOW = multi
        if ($transactionPartWithHigherAmount->getTransaction()->isSingleTransaction() && $transactionPartWithLowerAmount->getTransaction()->hasMultipleSide()){
            dump('HIGH = single | LOW = multi');
//            $this->updateHighSingleAndLowMultipleTransaction($transactionPartWithHigherAmount, $transactionPartWithLowerAmount);
            $user1 = $higherAmountTransaction->getDebtor();
            $user2 = $higherAmountTransaction->getLoaner();

            $updateDataCollection = $this->prepareUpdateDataSets($user1, $user2, $higherAmountTransaction, $lowerAmountTransaction);
            $updateDataCollection->setStateTransactionHigh(Transaction::STATE_ACCEPTED);
            if($updateDataCollection->getTransactionHighData()->getAmount() == 0.0){
                $updateDataCollection->setStateTransactionHigh(Transaction::STATE_CLEARED);
            }

            $updateDataCollection->setStateTransactionLow(Transaction::STATE_PARTIAL_CLEARED);
            if($updateDataCollection->getTransactionLowData()->getAmount() == 0.0){
                $updateDataCollection->setStateTransactionLow(Transaction::STATE_CLEARED);
            }

            $this->transactionService->update($updateDataCollection->getTransactionHigh(), $updateDataCollection->getTransactionHighData());
            $this->transactionService->update($updateDataCollection->getTransactionLow(), $updateDataCollection->getTransactionLowData());
        }
        // HIGH = multi | LOW = single
        if ($transactionPartWithHigherAmount->getTransaction()->hasMultipleSide() && $transactionPartWithLowerAmount->getTransaction()->isSingleTransaction()){
            dump('HIGH = multi | LOW = single');
//            $this->updateHighMultiAndLowSingleTransaction($transactionPartWithHigherAmount, $transactionPartWithLowerAmount);
            $user1 = $lowerAmountTransaction->getDebtor();
            $user2 = $lowerAmountTransaction->getLoaner();

            // do in everyCase
            $updateDataCollection = $this->prepareUpdateDataSets($user1, $user2, $higherAmountTransaction, $lowerAmountTransaction);
            $updateDataCollection->setStateTransactionHigh(Transaction::STATE_PARTIAL_CLEARED);
            if($updateDataCollection->getTransactionHighData()->getAmount() == 0.0){
                $updateDataCollection->setStateTransactionHigh(Transaction::STATE_CLEARED);
            }

            $updateDataCollection->setStateTransactionLow(Transaction::STATE_ACCEPTED);
            if($updateDataCollection->getTransactionLowData()->getAmount() == 0.0){
                $updateDataCollection->setStateTransactionLow(Transaction::STATE_CLEARED);
            }

            $this->transactionService->update($updateDataCollection->getTransactionHigh(), $updateDataCollection->getTransactionHighData());
            $this->transactionService->update($updateDataCollection->getTransactionLow(), $updateDataCollection->getTransactionLowData());
        }
        // HIGH = multi | LOW = multi
        if ($transactionPartWithHigherAmount->getTransaction()->hasMultipleSide() && $transactionPartWithLowerAmount->getTransaction()->hasMultipleSide()){
            dump('HIGH = multi | LOW = multi');
            // askingUser is owner of both parts => one is his loan and other is his debt
            $askingUser = $transactionPartWithHigherAmount->getOwner();
            // next we have to find an exchange partner => this is a user that has a part in both corresponding debts or loans array
            $correspondingTransactionPartsHigh = $higherAmountTransaction->getDebts();
            if ($transactionPartWithHigherAmount->isDebt()){
                $correspondingTransactionPartsHigh = $higherAmountTransaction->getLoans();
            }
            $correspondingTransactionPartsLow = $lowerAmountTransaction->getDebts();
            if ($transactionPartWithLowerAmount->isDebt()){
                $correspondingTransactionPartsLow = $lowerAmountTransaction->getLoans();
            }

            // find parts of users, that exist in both arrays
            $exchangeCandidates = [];
            foreach ($correspondingTransactionPartsHigh->toArray() as $transactionPart) {
                /** @var TransactionPartInterface $transactionPart */
                foreach ($correspondingTransactionPartsLow->toArray() as $candidate) {
                    /** @var TransactionPartInterface $candidate */
                    if($transactionPart->getOwner() === $candidate->getOwner() && $transactionPart->getOwner() !== $askingUser){
                        $exchangeCandidates[] = $transactionPart->getOwner();
                    }
                }
            }

            // TODO we use the first exchange candidate to keep it simple. But it would be also an option
            // to search for the highest one or give the requester the option to choose
            // do in everyCase
            $updateDataCollection = $this->prepareUpdateDataSets($askingUser, $exchangeCandidates[0], $higherAmountTransaction, $lowerAmountTransaction);
            $updateDataCollection->setStateTransactionHigh(Transaction::STATE_PARTIAL_CLEARED);
            if($updateDataCollection->getTransactionHighData()->getAmount() == 0.0){
                $updateDataCollection->setStateTransactionHigh(Transaction::STATE_CLEARED);
            }

            $updateDataCollection->setStateTransactionLow(Transaction::STATE_PARTIAL_CLEARED);
            if($updateDataCollection->getTransactionLowData()->getAmount() == 0.0){
                $updateDataCollection->setStateTransactionLow(Transaction::STATE_CLEARED);
            }

            $this->transactionService->update($updateDataCollection->getTransactionHigh(), $updateDataCollection->getTransactionHighData());
            $this->transactionService->update($updateDataCollection->getTransactionLow(), $updateDataCollection->getTransactionLowData());
        }
    }

    /**
     * @deprecated is no longer used
     * @param TransactionPartInterface $transactionPartWithHigherAmount
     * @param TransactionPartInterface $transactionPartWithLowerAmount
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function updateHighSingleAndLowMultipleTransaction(TransactionPartInterface $transactionPartWithHigherAmount, TransactionPartInterface $transactionPartWithLowerAmount)
    {
        // HIGH = single | LOW = multi
        $multiTransaction = $transactionPartWithLowerAmount->getTransaction();
        $singleTransaction = $transactionPartWithHigherAmount->getTransaction();

        // get transactionPartners of single transaction
        $singleTransaction->getLoaner();
        $singleTransaction->getDebtor();

        // this is the user who is corresponding part of the high single transaction
        $singleTransactionPartner = $this->getTransactionPartnerOfSingleTransaction($singleTransaction, $transactionPartWithHigherAmount->getOwner());

        // we need the transactionPart of the multi transaction that corresponds to our single transaction part
        $multiTransactionPart = $this->getCorrespondingTransactionPartFromMultiTransaction($transactionPartWithLowerAmount, $multiTransaction, $singleTransactionPartner);

        $amountToReduce = $multiTransactionPart->getAmount();
        dump($amountToReduce);

        $multiTransactionPartnerPart = $transactionPartWithLowerAmount->getTransaction()->getTransactionPartByUser($singleTransactionPartner);
        $multiTransactionPartnerPartData = $this->getTransactionPartUpdateData($multiTransactionPartnerPart);
        $multiTransactionPartnerPartData->setAmount($multiTransactionPartnerPart->getAmount() - $amountToReduce);
        $multiTransactionPartnerPartData->setState(Transaction::STATE_ACCEPTED);
        $this->updateTransactionPart($multiTransactionPartnerPart, $multiTransactionPartnerPartData);

        $multiTransactionUpdateData = (new TransactionUpdateData())->initFrom($multiTransaction);
        $multiTransactionUpdateData->setState(Transaction::STATE_PARTIAL_CLEARED);
        $multiTransactionUpdateData->setAmount($multiTransaction->getAmount() - $amountToReduce);

        $transactionPartHighUpdateData = $this->getTransactionPartUpdateData($transactionPartWithHigherAmount);
        $transactionPartHighUpdateData->setAmount($transactionPartWithHigherAmount->getAmount() - $amountToReduce);
        $this->updateTransactionPart($transactionPartWithHigherAmount, $transactionPartHighUpdateData);

        $transactionPartLowUpdateData = $this->getTransactionPartUpdateData($transactionPartWithLowerAmount);
        $transactionPartLowUpdateData->setAmount($transactionPartWithLowerAmount->getAmount() - $amountToReduce);
        $this->updateTransactionPart($transactionPartWithLowerAmount, $transactionPartLowUpdateData);

        $singleTransactionUpdateData = (new TransactionUpdateData())->initFrom($singleTransaction);
        $singleTransactionUpdateData->setAmount($singleTransaction->getAmount() - $amountToReduce);
        $singleTransactionUpdateData->setState(Transaction::STATE_ACCEPTED);

        $multiTransactionPartUpdateData = $this->getTransactionPartUpdateData($multiTransactionPart);
        $multiTransactionPartUpdateData->setAmount(0.0);
        $multiTransactionPartUpdateData->setState(Transaction::STATE_CLEARED);
        $this->updateTransactionPart($multiTransactionPart, $multiTransactionPartUpdateData);

        $this->transactionService->update($multiTransaction, $multiTransactionUpdateData);
        $this->transactionService->updateInclusive($singleTransaction, $singleTransactionUpdateData);
    }

    /**
     * @deprecated is no longer used
     * @param TransactionPartInterface $transactionPartWithHigherAmount
     * @param TransactionPartInterface $transactionPartWithLowerAmount
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    private function updateHighMultiAndLowSingleTransaction(TransactionPartInterface $transactionPartWithHigherAmount, TransactionPartInterface $transactionPartWithLowerAmount)
    {
        // HIGH = multi | LOW = single
        $multiTransaction = $transactionPartWithHigherAmount->getTransaction();
        $multiTransactionUpdateData = (new TransactionUpdateData())->initFrom($multiTransaction);
        $multiTransactionUpdateData->setState(Transaction::STATE_PARTIAL_CLEARED);
        $multiTransactionUpdateData->setAmount($multiTransaction->getAmount() - $transactionPartWithLowerAmount->getAmount());
        $transactionPartHighUpdateData = $this->getTransactionPartUpdateData($transactionPartWithHigherAmount);
        $transactionPartHighUpdateData->setAmount($transactionPartWithHigherAmount->getAmount() - $transactionPartWithLowerAmount->getAmount());
        $this->updateTransactionPart($transactionPartWithHigherAmount, $transactionPartHighUpdateData);

        $singleTransaction = $transactionPartWithLowerAmount->getTransaction();
        $singleTransactionUpdateData = (new TransactionUpdateData())->initFrom($singleTransaction);
        $singleTransactionUpdateData->setAmount(0.0);
        $singleTransactionUpdateData->setState(Transaction::STATE_CLEARED);

        // we have to find debt5 from multi and reduce it by 5
        $singleTransactionPartner = $this->getTransactionPartnerOfSingleTransaction($singleTransaction, $transactionPartWithHigherAmount->getOwner());
        $multiTransactionPart = $this->getCorrespondingTransactionPartFromMultiTransaction($transactionPartWithLowerAmount, $multiTransaction, $singleTransactionPartner);
        $multiTransactionPartUpdateData = $this->getTransactionPartUpdateData($multiTransactionPart);
        $multiTransactionPartUpdateData->setAmount($multiTransactionPart->getAmount() - $transactionPartWithLowerAmount->getAmount());
        $this->updateTransactionPart($multiTransactionPart, $multiTransactionPartUpdateData);

        $this->transactionService->update($multiTransaction, $multiTransactionUpdateData);
        // using inclusive update will propagate amount, reason and state to belonging debts and loans
        $this->transactionService->updateInclusive($singleTransaction, $singleTransactionUpdateData);
    }

    /**
     * @deprecated is dont used anymore
     * @param Transaction $transactionWithHigherAmount
     * @param Transaction $transactionWithLowerAmount
     * @return void
     */
    private function updateSingleTransactions(Transaction $transactionWithHigherAmount, Transaction $transactionWithLowerAmount){
        // create updateTransactionDataSets
        $transactionUpdateDataHigherAmount = (new TransactionUpdateData())->initFrom($transactionWithHigherAmount);
        $transactionUpdateDataLowerAmount = (new TransactionUpdateData())->initFrom($transactionWithLowerAmount);

        $difference = $transactionWithHigherAmount->getAmount() - $transactionWithLowerAmount->getAmount();

        // HIGHER => original amount - difference
        $transactionUpdateDataHigherAmount->setAmount($difference);
        // LOWER => 0
        $transactionUpdateDataLowerAmount->setAmount(0);

        //HIGHER => ACCEPTED
        $transactionUpdateDataHigherAmount->setState(Transaction::STATE_ACCEPTED);
        //LOWER => CLEARED cause its 0 now
        $transactionUpdateDataLowerAmount->setState(Transaction::STATE_CLEARED);

        // using inclusive update will propagate amount, reason and state to belonging debts and loans
        $this->transactionService->updateInclusive($transactionWithHigherAmount, $transactionUpdateDataHigherAmount);
        $this->transactionService->updateInclusive($transactionWithLowerAmount, $transactionUpdateDataLowerAmount);
    }

    private function updateTransactionPart(TransactionPartInterface $transactionPart, TransactionPartDataInterface $transactionPartData): void
    {
        if($transactionPart->isDebt()){
            $this->debtService->update($transactionPart, $transactionPartData);
        }
        $this->loanService->update($transactionPart, $transactionPartData);
    }

    private function getTransactionPartUpdateData(TransactionPartInterface $transactionPart): TransactionPartDataInterface
    {
        if($transactionPart->isDebt()){
            return (new DebtUpdateData())->initFrom($transactionPart);
        }
        return (new LoanUpdateData())->initFrom($transactionPart);
    }

    private function getTransactionPartnerOfSingleTransaction(Transaction $singleTransaction, User $partner){
        if ($singleTransaction->getDebtor() === $partner){
            return $singleTransaction->getLoaner();
        }
        return $singleTransaction->getDebtor();
    }

    private function clearLowerTransactionPart(TransactionPartInterface $transactionPartWithLowerAmount)
    {
        dump($transactionPartWithLowerAmount);
        $updateData = $this->getTransactionPartUpdateData($transactionPartWithLowerAmount);
        $updateData->setState(Transaction::STATE_CLEARED);
        $updateData->setAmount(0.0);
        if ($updateData instanceof DebtUpdateData){
            $this->debtService->update($transactionPartWithLowerAmount, $updateData);
        }
        else{
            $this->loanService->update($transactionPartWithLowerAmount, $updateData);
        }
    }

    /**
     * @param TransactionPartInterface $transactionPartOfMultiTransaction
     * @param Transaction $multiTransaction
     * @param User $singleTransactionPartner
     * @return TransactionPartInterface
     */
    private function getCorrespondingTransactionPartFromMultiTransaction(TransactionPartInterface $transactionPartOfMultiTransaction, Transaction $multiTransaction, User $singleTransactionPartner)
    {
        // be aware of that getTransactionPartByUser first iterates over debts and second over loaners
        dump($singleTransactionPartner->getId());
        $multiTransactionPart = $multiTransaction->getTransactionPartByUser($singleTransactionPartner);
        dump($transactionPartOfMultiTransaction->getAmount());
        dump($multiTransactionPart->getAmount());
        if ($transactionPartOfMultiTransaction->getTransaction()->hasMultipleDebtors()) {
            dump('Multiple Debtors: Change multiTransactionPart');
            $multiTransactionPart = $transactionPartOfMultiTransaction;
        }
        return $multiTransactionPart;
    }

    /**
     * @param User $user1
     * @param User $user2
     * @param Transaction $higherAmountTransaction
     * @param Transaction $lowerAmountTransaction
     * @return TransactionPartInterface[]
     */
    private function findEffectedTransactionParts(User $user1, User $user2, Transaction $higherAmountTransaction, Transaction $lowerAmountTransaction)
    {
        $transactionParts = [];
        $counter = 0;
        foreach($higherAmountTransaction->getLoans() as $loan){
            if ($loan->getOwner() == $user1 || $loan->getOwner() == $user2){
                $transactionParts[$counter] = $loan;
                $counter++;
            }
        }
        foreach($higherAmountTransaction->getDebts() as $debt){
            if ($debt->getOwner() == $user1 || $debt->getOwner() == $user2){
                $transactionParts[$counter] = $debt;
                $counter++;
            }
        }

        foreach($lowerAmountTransaction->getLoans() as $loan){
            if ($loan->getOwner() == $user1 || $loan->getOwner() == $user2){
                $transactionParts[$counter] = $loan;
                $counter++;
            }
        }
        foreach($lowerAmountTransaction->getDebts() as $debt){
            if ($debt->getOwner() == $user1 || $debt->getOwner() == $user2){
                $transactionParts[$counter] = $debt;
                $counter++;
            }
        }
        return $transactionParts;
    }

    /**
     * @param User $user1
     * @param User $user2
     * @param Transaction $higherAmountTransaction
     * @param Transaction $lowerAmountTransaction
     * @return TransactionUpdateDataCollection
     */
    private function prepareUpdateDataSets(User $user1, User $user2, Transaction $higherAmountTransaction, Transaction $lowerAmountTransaction): TransactionUpdateDataCollection
    {
        $collection = new TransactionUpdateDataCollection();

        $activeTransactionParts = $this->findEffectedTransactionParts($user1, $user2, $higherAmountTransaction, $lowerAmountTransaction);
        $lowestAmount = PHP_INT_MAX;
        foreach ($activeTransactionParts as $part) {
            if ($part->getAmount() < $lowestAmount) {
                $lowestAmount = $part->getAmount();
            }
        }

        // transaction Updates
        $transactionUpdateDataHigh = (new TransactionUpdateData())->initFrom($higherAmountTransaction);
        $transactionUpdateDataHigh->setAmount($transactionUpdateDataHigh->getAmount() - $lowestAmount);
        $collection->setTransactionHighData($transactionUpdateDataHigh);
        $collection->setTransactionHigh($higherAmountTransaction);
        $transactionUpdateDataLow = (new TransactionUpdateData())->initFrom($lowerAmountTransaction);
        $transactionUpdateDataLow->setAmount($transactionUpdateDataLow->getAmount() - $lowestAmount);
        $collection->setTransactionLowData($transactionUpdateDataLow);
        $collection->setTransactionLow($lowerAmountTransaction);

        // transactionPart Updates
        foreach ($activeTransactionParts as $part) {
            $transactionPartUpdateDataSet = $this->getTransactionPartUpdateData($part);
            $transactionPartUpdateDataSet->setAmount($transactionPartUpdateDataSet->getAmount() - $lowestAmount);
            $transactionPartUpdateDataSet->setState(Transaction::STATE_ACCEPTED);

            if ($transactionPartUpdateDataSet->getAmount() == 0.0){
                $transactionPartUpdateDataSet->setState(Transaction::STATE_CLEARED);
            }

            // set to collection
            if ($part->getTransaction() === $higherAmountTransaction && $part->isLoan()){
                $collection->setTransactionPartHighLoanData($transactionPartUpdateDataSet);
                $collection->setTransactionPartHighLoan($part);
            }
            if ($part->getTransaction() === $higherAmountTransaction && $part->isDebt()){
                $collection->setTransactionPartHighDebtData($transactionPartUpdateDataSet);
                $collection->setTransactionPartHighDebt($part);
            }
            if ($part->getTransaction() === $lowerAmountTransaction && $part->isLoan()){
                $collection->setTransactionPartLowLoanData($transactionPartUpdateDataSet);
                $collection->setTransactionPartLowLoan($part);
            }
            if ($part->getTransaction() === $lowerAmountTransaction && $part->isDebt()){
                $collection->setTransactionPartLowDebtData($transactionPartUpdateDataSet);
                $collection->setTransactionPartLowDebt($part);
            }

            $this->updateTransactionPart($part, $transactionPartUpdateDataSet);
        }

        return $collection;
    }
}
