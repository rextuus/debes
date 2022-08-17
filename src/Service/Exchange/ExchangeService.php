<?php

namespace App\Service\Exchange;

use App\Entity\Debt;
use App\Entity\Exchange;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\TransactionPartInterface;
use App\Repository\ExchangeRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

/**
 * ExchangeService
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class ExchangeService
{
    /**
     * @var ExchangeFactory
     */
    private $exchangeFactory;

    /**
     * @var ExchangeRepository
     */
    private $exchangeRepository;

    /**
     * LoanService constructor.
     *
     * @param ExchangeFactory    $exchangeFactory
     * @param ExchangeRepository $exchangeRepository
     */
    public function __construct(
        ExchangeFactory $exchangeFactory,
        ExchangeRepository $exchangeRepository
    ) {
        $this->exchangeFactory = $exchangeFactory;
        $this->exchangeRepository = $exchangeRepository;
    }

    /**
     * storeExchange
     *
     * @param ExchangeCreateData $transactionData
     * @param bool               $persist
     *
     * @return Exchange
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function storeExchange(ExchangeCreateData $transactionData, bool $persist = true): Exchange
    {
        $transaction = $this->exchangeFactory->createByData($transactionData);

        if ($persist) {
            $this->exchangeRepository->persist($transaction);
        }

        return $transaction;
    }

    /**
     * update
     *
     * @param Exchange           $transaction
     * @param ExchangeUpdateData $data
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(Exchange $transaction, ExchangeUpdateData $data): void
    {
        $this->exchangeFactory->mapData($transaction, $data);

        $this->exchangeRepository->persist($transaction);
    }

    /**
     * getAllExchangesBelongingToTransaction
     *
     * @param Transaction $transaction
     *
     * @return Exchange[]
     *
     */
    public function getAllExchangesBelongingToTransaction(Transaction $transaction): array
    {
        return $this->exchangeRepository->findBy(['transaction' => $transaction]);
    }

    /**
     * @param Exchange $exchange
     * @return Transaction
     */
    public function getCorrespondingExchangeTransaction(Exchange $exchange): Transaction
    {
        return $this->exchangeRepository->findCorrespondingExchange(
            $exchange->getTransaction(),
            $exchange->getDebt(),
            $exchange->getLoan()
        );
    }


    /**
     * getAllExchangesBelongingToTransactionAndPartType
     *
     * @param Transaction              $transaction
     * @param TransactionPartInterface $transactionPart
     * @param bool                     $isDebt
     *
     * @return Exchange[]
     */
    public function getAllExchangesBelongingToTransactionAndPartType(
        Transaction $transaction,
        TransactionPartInterface $transactionPart,
        bool $isDebt = true
    ): array {
        if ($isDebt){
            return $this->exchangeRepository->findBy(['transaction' => $transaction, 'loan' => $transactionPart]);
        }else{
            return $this->exchangeRepository->findBy(['transaction' => $transaction, 'debt' => $transactionPart]);
        }
    }

    public function findById(int $int)
    {
        return $this->exchangeRepository->find($int);
    }
}
