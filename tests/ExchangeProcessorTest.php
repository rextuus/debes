<?php

namespace App\Tests;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\DebtRepository;
use App\Repository\ExchangeRepository;
use App\Repository\UserRepository;
use App\Service\Exchange\ExchangeService;
use App\Service\Transfer\ExchangeProcessor;

/**
 * ExchangeProcessorTest
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class ExchangeProcessorTest extends FixtureTestCase
{

    /**
     * @var ExchangeProcessor
     */
    private $exchangeProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loadFixtureFiles(
            [
                __DIR__ . '/users.yml',
                __DIR__.'/transactions.yml'
            ]
        );
        $this->exchangeProcessor = $this->getService(ExchangeProcessor::class);
    }

    protected function tearDown(): void
    {

        parent::tearDown();
    }


    public function testFindExchangeCandidatesForTransaction(): void
    {
        // transaction1: user1 => 20€ => user2
        // transaction2: user2 => 30€ => user1
        // transaction3: user2 => 10€ => user1
        // transaction4: user2 => 15€ => user3
        // transaction2 has bigger amount than transaction1 => we can exchange them
        // transaction3 has smaller amount than transaction1 => we can exchange them
        // transaction4 has nothing to do with user 1 => we cant exchange them

        /** @var Transaction $transactionToFindExchangeFor */
        $transactionToFindExchangeFor = $this->getFixtureEntity(Transaction::class, 1);

        /** @var Transaction $fittingTransaction */
        $fittingTransaction = $this->getFixtureEntity(Transaction::class, 2);

        /** @var Transaction $fittingTransaction2 */
        $fittingTransaction2 = $this->getFixtureEntity(Transaction::class, 3);

        $candidateSets = $this->exchangeProcessor->findExchangeCandidatesForTransactionPart($transactionToFindExchangeFor->getDebts()[0]);

        $fittingCandidates = $candidateSets->getFittingCandidates();
        $this->assertCount(2, $fittingCandidates);

        $expectedTransactionIds = [$fittingTransaction->getId(), $fittingTransaction2->getId()];
        foreach ($fittingCandidates as $candidate){
            $this->assertContains($candidate->getTransactionId(), $expectedTransactionIds);
        }
    }

    /**
     * SINGLE < SINGLE
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testCalculateExchangeBetweenSingleTransactionsWhenAskingTransactionIsLower()
    {
        // transaction1: user1 => 20€ => user2
        // transaction2: user2 => 30€ => user1

        /** @var ExchangeRepository $exchangeRepository */
        $exchangeRepository = $this->getService(ExchangeRepository::class);

        $exchangesBefore = $exchangeRepository->count([]);

        // transaction1 [debt1][loan2] = 20€
        /** @var Transaction $transaction1 */
        $transaction1 = $this->getFixtureEntity(Transaction::class, 1);

        /** @var Debt $debt1 */
        $debt1 = $this->getFixtureEntity(Debt::class, 1);

        /** @var Loan $loan1 */
        $loan1 = $this->getFixtureEntity(Loan::class, 1);

        // transaction2 [debt2][loan1] = 30€
        /** @var Transaction $transaction2 */
        $transaction2 = $this->getFixtureEntity(Transaction::class, 2);

        /** @var Debt $debt2 */
        $debt2 = $this->getFixtureEntity(Debt::class, 2);

        /** @var Loan $loan2 */
        $loan2 = $this->getFixtureEntity(Loan::class, 2);

        $this->exchangeProcessor->exchangeTransactionParts($debt1, $loan2);
        $exchangesAfter = $exchangeRepository->count([]);

        // there will be 2 new exchanges created for each of both transactions
        $this->assertEquals($exchangesBefore+2, $exchangesAfter);
        $newExchange1 = $exchangeRepository->find($exchangesBefore+1);
        $newExchange2 = $exchangeRepository->find($exchangesBefore+2);

        // transaction1 should be cleared by amount of 20 and remain 0
        $this->assertEquals($transaction1, $newExchange2->getTransaction());
        $this->assertEquals(20.0, $newExchange2->getAmount());
        $this->assertEquals(0.0, $newExchange2->getRemainingAmount());
        // fitting transaction should be cleared by amount of 20 and remain 10
        $this->assertEquals($transaction2, $newExchange1->getTransaction());
        $this->assertEquals(20.0, $newExchange1->getAmount());
        $this->assertEquals(10.0, $newExchange1->getRemainingAmount());

        // transaction1 has amount of 20 => should be 0 afterwards
        // transaction2 has amount of 30 => should be 10 afterwards
        $this->assertEquals(0.0, $transaction1->getAmount());
        $this->assertEquals(10.0, $transaction2->getAmount());

        // check if debt and loans are updated
        $this->assertEquals(0.0, $debt1->getAmount());
        $this->assertEquals(0.0, $loan1->getAmount());
        $this->assertEquals(10.0, $debt2->getAmount());
        $this->assertEquals(10.0, $loan2->getAmount());
        $this->assertEquals(Transaction::STATE_CLEARED, $debt1->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $loan1->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt2->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt2->getState());
    }

    /**
     * SINGLE > SINGLE
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function testCalculateExchangeBetweenSingleTransactionsWhenAskingTransactionIsHigher()
    {
        // transaction1: user1 => 20€ => user2
        // transaction2: user2 => 30€ => user1

        /** @var ExchangeRepository $exchangeRepository */
        $exchangeRepository = $this->getService(ExchangeRepository::class);

        $exchangesBefore = $exchangeRepository->count([]);

        // transaction1 [debt1][loan2] = 20€
        /** @var Transaction $transaction1 */
        $transaction1 = $this->getFixtureEntity(Transaction::class, 1);

        /** @var Debt $debt1 */
        $debt1 = $this->getFixtureEntity(Debt::class, 1);

        /** @var Loan $loan1 */
        $loan1 = $this->getFixtureEntity(Loan::class, 1);

        // transaction2 [debt2][loan1] = 30€
        /** @var Transaction $transaction2 */
        $transaction2 = $this->getFixtureEntity(Transaction::class, 2);

        /** @var Debt $debt2 */
        $debt2 = $this->getFixtureEntity(Debt::class, 2);

        /** @var Loan $loan2 */
        $loan2 = $this->getFixtureEntity(Loan::class, 2);

        $this->exchangeProcessor->exchangeTransactionParts($debt2, $loan1);
        $exchangesAfter = $exchangeRepository->count([]);

        // there will be 2 new exchanges created for each of both transactions
        $this->assertEquals($exchangesBefore+2, $exchangesAfter);
        $newExchange1 = $exchangeRepository->find($exchangesBefore+1);
        $newExchange2 = $exchangeRepository->find($exchangesBefore+2);

        // transaction1 should be cleared by amount of 20 and remain 0
        $this->assertEquals($transaction1, $newExchange2->getTransaction());
        $this->assertEquals(20.0, $newExchange2->getAmount());
        $this->assertEquals(0.0, $newExchange2->getRemainingAmount());
        // fitting transaction should be cleared by amount of 20 and remain 10
        $this->assertEquals($transaction2, $newExchange1->getTransaction());
        $this->assertEquals(20.0, $newExchange1->getAmount());
        $this->assertEquals(10.0, $newExchange1->getRemainingAmount());

        // transaction1 has amount of 20 => should be 0 afterwards
        // transaction2 has amount of 30 => should be 10 afterwards
        $this->assertEquals(0.0, $transaction1->getAmount());
        $this->assertEquals(10.0, $transaction2->getAmount());

        // check if debt and loans are updated
        $this->assertEquals(0.0, $debt1->getAmount());
        $this->assertEquals(0.0, $loan1->getAmount());
        $this->assertEquals(10.0, $debt2->getAmount());
        $this->assertEquals(10.0, $loan2->getAmount());
        $this->assertEquals(Transaction::STATE_CLEARED, $debt1->getState());
        $this->assertEquals(Transaction::STATE_CLEARED, $loan1->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt2->getState());
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt2->getState());
    }
}
