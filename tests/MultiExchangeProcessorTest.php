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
 * MultiExchangeProcessorTest
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class MultiExchangeProcessorTest extends FixtureTestCase
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
                __DIR__.'/multi_transactions.yml'
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
        // transaction1: user2,3,4 => 90€ => user1
        // transaction2: user1 => 180€ => user5,6,7
        // transaction3: user5,6,7 => 10€ => user2,3,4
        // transaction4: user5 => 5€ => user1
        // transaction5: user6 => 200€ => user1
        // transaction6: user1 => 200€ => user2
        // we search for debt1 which is part of transaction2
        // so we expect to find loan1 and loan1_2 and loan 1_3 and loan1_4 and loan1_5
        // these are located in transaction1 and transaction4 transaction5 why this should be the only transactionIds

        /** @var Transaction $singleLoanerMultipleDebtorsTransaction */
        $singleLoanerMultipleDebtorsTransaction = $this->getFixtureEntityByIdent('transactionSingleLoanerMultipleDebtors');
        /** @var Transaction $singleTransaction */
        $singleTransaction = $this->getFixtureEntityByIdent('singleTransactionLow');
        /** @var Transaction $singleTransactionHigh */
        $singleTransactionHigh = $this->getFixtureEntityByIdent('singleTransactionHigh');
        /** @var Transaction $transactionMultipleLoanersMultipleDebtors2 */
        $transactionMultipleLoanersMultipleDebtors2 = $this->getFixtureEntityByIdent('transactionMultipleLoanersMultipleDebtors2');
        /** @var Transaction $transactionMultipleLoanersMultipleDebtors3 */
        $transactionMultipleLoanersMultipleDebtors3 = $this->getFixtureEntityByIdent('transactionMultipleLoanersMultipleDebtors3');
        /** @var Debt $transactionPart */
        $transactionPart = $this->getFixtureEntityByIdent('debt1');

        $candidateSets = $this->exchangeProcessor->findExchangeCandidatesForTransactionPart($transactionPart);

        $fittingCandidates = $candidateSets->getFittingCandidatesDtoVersion();
        $this->assertCount(5, $fittingCandidates);

        $expectedTransactionIds = [
            $singleLoanerMultipleDebtorsTransaction->getId(),
            $singleTransaction->getId(),
            $singleTransactionHigh->getId(),
            $transactionMultipleLoanersMultipleDebtors2->getId(),
            $transactionMultipleLoanersMultipleDebtors3->getId()
        ];
        foreach ($fittingCandidates as $candidate){
            $this->assertContains($candidate->getTransactionId(), $expectedTransactionIds);
        }
    }

    /**
     * HIGH = multi | LOW = single
     * S < 1-L
     */
    public function testCalculateExchangeCaseHighMultiAndLowLowTransactionParts(): void
    {
        // transaction1: user2,3,4 => 90€ => user1
        // transaction2: user1 => 180€ => user5,6,7
        // transaction3: user5,6,7 => 180€ => user2,3,4
        // transaction4: user5 => 5€ => user1
        // transaction4: user1 => 5€ => user2
        // transaction5: user6 => 200€ => user1
        // transaction6: user1 => 300€ => user2
        // transaction7: user2 => 5€ => user6
        // we will exchange debt1 (multi 180€) with loan1_2 (single 5€)

        // parts gave into function
        /** @var Debt $debt1 */
        $debt1 = $this->getFixtureEntityByIdent('debt1');
        /** @var Loan $loan1_2 */
        $loan1_2 = $this->getFixtureEntityByIdent('loan1_2');

        // parts affected
        /** @var Debt $debt5_2 */
        $debt5_2 = $this->getFixtureEntityByIdent('debt5_2');
        /** @var Loan $loan5 */
        $loan5 = $this->getFixtureEntityByIdent('loan5');

        // affected transactions
        /** @var Transaction $multiTransaction */
        $multiTransaction = $this->getFixtureEntityByIdent('transactionMultipleLoanersSingleDebtor');
        /** @var Transaction $singleTransaction */
        $singleTransaction = $this->getFixtureEntityByIdent('singleTransactionLow');

        $this->exchangeProcessor->exchangeTransactionParts($debt1, $loan1_2);

        // afterwards single Transaction should be killed and multi decreased by 5
        // check transactions
        $this->assertEquals(175.0, $multiTransaction->getAmount()); // before 180 minus 5 (value of loan1_2)
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $multiTransaction->getState()); // partial cleared now
        $this->assertEquals(0.0, $singleTransaction->getAmount()); // should be cleared
        $this->assertEquals(Transaction::STATE_CLEARED, $singleTransaction->getState()); // should be cleared

        // check transactionParts
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt1->getState()); // should be cleared
        $this->assertEquals(175.0, $debt1->getAmount()); // before 180 minus 5 (value of loan1_2)
        $this->assertEquals(Transaction::STATE_CLEARED, $loan1_2->getState()); // should be cleared
        $this->assertEquals(0.0, $loan1_2->getAmount()); // should be cleared
        $this->assertEquals(Transaction::STATE_CLEARED, $debt5_2->getState()); // should be cleared now
        $this->assertEquals(0.0, $debt5_2->getAmount()); // should be cleared
        $this->assertEquals(Transaction::STATE_ACCEPTED, $loan5->getState()); // should be cleared now
        $this->assertEquals(45.0, $loan5->getAmount()); // should be cleared

        // check exchange creation
    }

    /**
     * HIGH = multi | LOW = single
     * S < D-1
     */
    public function testCalculateExchangeCaseHighMultiAndLowLowTransactionPartsVariant2(): void
    {
        // transaction1: user2,3,4 => 90€ => user1
        // transaction2: user1 => 180€ => user5,6,7
        // transaction3: user5,6,7 => 180€ => user2,3,4
        // transaction4: user5 => 5€ => user1
        // transaction4: user1 => 5€ => user2
        // transaction5: user6 => 200€ => user1
        // transaction6: user1 => 300€ => user2
        // transaction7: user2 => 5€ => user6
        // we will exchange debt2 (multi 90€) with loan2_2 (single 5€)

        // parts gave into function
        /** @var Debt $debt2 */
        $debt2 = $this->getFixtureEntityByIdent('debt2');
        /** @var Loan $loan2_2 */
        $loan2_2 = $this->getFixtureEntityByIdent('loan2_2');

        // parts affected
        /** @var Debt $debt1_2 */
        $debt1_2 = $this->getFixtureEntityByIdent('debt1_2');
        /** @var Loan $loan1 */
        $loan1 = $this->getFixtureEntityByIdent('loan1');

        // affected transactions
        /** @var Transaction $multiTransaction */
        $multiTransaction = $this->getFixtureEntityByIdent('transactionSingleLoanerMultipleDebtors');
        /** @var Transaction $singleTransaction */
        $singleTransaction = $this->getFixtureEntityByIdent('singleTransactionLow2');

        $this->exchangeProcessor->exchangeTransactionParts($debt2, $loan2_2);

        // afterwards single Transaction should be killed and multi decreased by 5
        // check transactions
        $this->assertEquals(85.0, $multiTransaction->getAmount()); // before 180 minus 5 (value of loan1_2)
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $multiTransaction->getState()); // partial cleared now
        $this->assertEquals(0.0, $singleTransaction->getAmount()); // should be cleared
        $this->assertEquals(Transaction::STATE_CLEARED, $singleTransaction->getState()); // should be cleared

        // check transactionParts
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt2->getState()); // should be cleared
        $this->assertEquals(15.0, $debt2->getAmount()); // before 180 minus 5 (value of loan1_2)
        $this->assertEquals(Transaction::STATE_CLEARED, $loan2_2->getState()); // should be cleared
        $this->assertEquals(0.0, $loan2_2->getAmount()); // should be cleared
        $this->assertEquals(Transaction::STATE_ACCEPTED, $loan1->getState()); // should be cleared
        $this->assertEquals(85.0, $loan1->getAmount()); // before 90 - 5 = 85
        $this->assertEquals(Transaction::STATE_CLEARED, $debt1_2->getState()); // should be cleared
        $this->assertEquals(0.0, $debt1_2->getAmount()); // before 5 - 5 = 0

        // check exchange creation
    }

    /**
     * HIGH = multi | LOW = single
     * S < D-L
     */
    public function testCalculateExchangeCaseHighMultiAndLowLowTransactionPartsVariant3(): void
    {
        // transaction1: user2,3,4 => 90€ => user1
        // transaction2: user1 => 180€ => user5,6,7
        // transaction3: user5,6,7 => 180€ => user2,3,4
        // transaction4: user5 => 5€ => user1
        // transaction4: user1 => 5€ => user2
        // transaction5: user6 => 200€ => user1
        // transaction6: user1 => 300€ => user2
        // transaction7: user2 => 5€ => user6
        // we will exchange debt6 (multi 180€) with loan6_2 (single 5€)

        // parts gave into function
        /** @var Debt $debt6 */
        $debt6 = $this->getFixtureEntityByIdent('debt6');
        /** @var Loan $loan6_2 */
        $loan6_2 = $this->getFixtureEntityByIdent('loan6_2');

        // parts affected
        /** @var Debt $debt2_2 */
        $debt2_2 = $this->getFixtureEntityByIdent('debt2_2');
        /** @var Loan $loan2 */
        $loan2 = $this->getFixtureEntityByIdent('loan2');

        // affected transactions
        /** @var Transaction $multiTransaction */
        $multiTransaction = $this->getFixtureEntityByIdent('transactionMultipleLoanersMultipleDebtors');
        /** @var Transaction $singleTransaction */
        $singleTransaction = $this->getFixtureEntityByIdent('singleTransactionLow3');

        $this->exchangeProcessor->exchangeTransactionParts($debt6, $loan6_2);

        // afterwards single Transaction should be killed and multi decreased by 5
        // check transactions
        $this->assertEquals(175.0, $multiTransaction->getAmount()); // before 180 minus 5 (value of loan1_2)
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $multiTransaction->getState()); // partial cleared now
        $this->assertEquals(0.0, $singleTransaction->getAmount()); // should be cleared
        $this->assertEquals(Transaction::STATE_CLEARED, $singleTransaction->getState()); // should be cleared

        // check transactionParts
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt6->getState()); // should be cleared
        $this->assertEquals(55.0, $debt6->getAmount()); // before 60 minus 5 (value of loan1_2)
        $this->assertEquals(Transaction::STATE_CLEARED, $loan6_2->getState()); // should be cleared
        $this->assertEquals(0.0, $loan6_2->getAmount()); // should be cleared
        $this->assertEquals(Transaction::STATE_ACCEPTED, $loan2->getState()); // should be cleared
        $this->assertEquals(95.0, $loan2->getAmount()); // before 20 - 5 = 15
        $this->assertEquals(Transaction::STATE_CLEARED, $debt2_2->getState()); // should be cleared
        $this->assertEquals(0.0, $debt2_2->getAmount()); // before 5 - 5 = 0

        // check exchange creation
    }

    /**
     * HIGH = single | LOW = multi
     * S > 1-L
     */
    public function testCalculateExchangeCaseHighSingleAndLowMultiTransactionParts(): void
    {
        // transaction1: user2,3,4 => 90€ => user1
        // transaction2: user1 => 180€ => user5,6,7
        // transaction3: user5,6,7 => 180€ => user2,3,4
        // transaction4: user5 => 5€ => user1
        // transaction4: user1 => 5€ => user2
        // transaction5: user6 => 200€ => user1
        // transaction6: user1 => 300€ => user2
        // transaction7: user2 => 5€ => user6
        // we will exchange debt1 (multi 180€ | 60€ part of user6) with loan1_3 (single 200€)

        // parts gave into function
        /** @var Debt $debt1 */
        $debt1 = $this->getFixtureEntityByIdent('debt1');
        /** @var Loan $loan1_3 */
        $loan1_3 = $this->getFixtureEntityByIdent('loan1_3');

        // parts affected
        /** @var Loan $loan1_3 */
        $loan6 = $this->getFixtureEntityByIdent('loan6');
        /** @var Debt $debt6_2 */
        $debt6_2 = $this->getFixtureEntityByIdent('debt6_2');

        /** @var Transaction $multiTransaction */
        $multiTransaction = $this->getFixtureEntityByIdent('transactionMultipleLoanersSingleDebtor');
        /** @var Transaction $singleTransaction */
        $singleTransaction = $this->getFixtureEntityByIdent('singleTransactionHigh');

        $this->exchangeProcessor->exchangeTransactionParts($debt1, $loan1_3);

        // afterwards single Transaction should be killed and multi decreased by 5
        // check transactions
        $this->assertEquals(120.0, $multiTransaction->getAmount()); // before 180 minus 60 (value of loan6)
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $multiTransaction->getState()); // partial cleared now
        $this->assertEquals(140.0, $singleTransaction->getAmount()); // before 200 minus 60 (part of loan6)
        $this->assertEquals(Transaction::STATE_ACCEPTED, $singleTransaction->getState()); // should be cleared

        // check transactionParts
        $this->assertEquals(120.0, $debt1->getAmount()); // before 180 minus 60 (value of loan6)
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt1->getState()); // should be accepted further
        $this->assertEquals(140.0, $loan1_3->getAmount()); // before 200 minus 60 (part of loan6)
        $this->assertEquals(Transaction::STATE_ACCEPTED, $loan1_3->getState()); // should be accepted further
        $this->assertEquals(0.0, $loan6->getAmount()); // 60 - 60 = 0
        $this->assertEquals(Transaction::STATE_CLEARED, $loan6->getState()); // should be cleared
        $this->assertEquals(140.0, $debt6_2->getAmount()); // 200 - 60 = 140
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt6_2->getState()); // should be cleared

        // check exchange creation
    }

    /**
     * HIGH = single | LOW = multi
     * S > D-1
     */
    public function testCalculateExchangeCaseHighSingleAndLowMultiTransactionParts2(): void
    {
        // transaction1: user2,3,4 => 90€ => user1
        // transaction2: user1 => 180€ => user5,6,7
        // transaction3: user5,6,7 => 180€ => user2,3,4
        // transaction4: user5 => 5€ => user1
        // transaction4: user1 => 5€ => user2
        // transaction5: user6 => 200€ => user1
        // transaction6: user1 => 300€ => user2
        // transaction7: user2 => 5€ => user6
        // we will exchange debt2 (multi 90€ | 20€ part of user2) with loan2_3 (single 300€)

        // parts gave into function
        /** @var Debt $debt2 */
        $debt2 = $this->getFixtureEntityByIdent('debt2');
        /** @var Loan $loan2_3 */
        $loan2_3 = $this->getFixtureEntityByIdent('loan2_3');

        // parts affected
        /** @var Loan $loan1 */
        $loan1 = $this->getFixtureEntityByIdent('loan1');
        /** @var Debt $debt1_3 */
        $debt1_3 = $this->getFixtureEntityByIdent('debt1_3');

        /** @var Transaction $multiTransaction */
        $multiTransaction = $this->getFixtureEntityByIdent('transactionSingleLoanerMultipleDebtors');
        /** @var Transaction $singleTransaction */
        $singleTransaction = $this->getFixtureEntityByIdent('singleTransactionHigh2');

        $this->exchangeProcessor->exchangeTransactionParts($debt2, $loan2_3);

        // afterwards single Transaction should be killed and multi decreased by 5
        // check transactions
        $this->assertEquals(70.0, $multiTransaction->getAmount()); // before 90 minus 20 (value of debt2)
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $multiTransaction->getState()); // partial cleared now
        $this->assertEquals(280.0, $singleTransaction->getAmount()); // before 300 minus 20 (part of debt2)
        $this->assertEquals(Transaction::STATE_ACCEPTED, $singleTransaction->getState()); // should be cleared

        // check transactionParts
        $this->assertEquals(0.0, $debt2->getAmount()); // before 20 minus 300 (value of loan2_3)
        $this->assertEquals(Transaction::STATE_CLEARED, $debt2->getState()); // should be accepted further
        $this->assertEquals(280.0, $loan2_3->getAmount()); // before 300 minus 20 (part of loan6)
        $this->assertEquals(Transaction::STATE_ACCEPTED, $loan2_3->getState()); // should be accepted further
        $this->assertEquals(70.0, $loan1->getAmount()); // before 90 minus 20 = 70
        $this->assertEquals(Transaction::STATE_ACCEPTED, $loan1->getState()); // should be cleared
        $this->assertEquals(280.0, $debt1_3->getAmount()); // before 300 minus 20 = 280
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt1_3->getState()); // should be cleared

        // check exchange creation
    }

    /**
     * HIGH = single | LOW = multi
     * S > D-L
     */
    public function testCalculateExchangeCaseHighSingleAndLowMultiTransactionParts3(): void
    {
        // transaction1: user2,3,4 => 90€ => user1
        // transaction2: user1 => 180€ => user5,6,7
        // transaction3: user5,6,7 => 180€ => user2,3,4
        // transaction4: user5 => 5€ => user1
        // transaction4: user1 => 5€ => user2
        // transaction5: user6 => 200€ => user1
        // transaction6: user1 => 300€ => user2
        // transaction7: user2 => 5€ => user6
        // transaction8: user4 => 300€ => user7
        // we will exchange debt7 (multi 180€ | 70€ part of user7) with loan7_2 (single 300€)

        // parts gave into function
        /** @var Debt $debt7 */
        $debt7 = $this->getFixtureEntityByIdent('debt7');
        /** @var Loan $loan7_2 */
        $loan7_2 = $this->getFixtureEntityByIdent('loan7_2');

        // parts affected
        /** @var Loan $loan4 */
        $loan4 = $this->getFixtureEntityByIdent('loan4');
        /** @var Debt $debt4_2 */
        $debt4_2 = $this->getFixtureEntityByIdent('debt4_2');

        /** @var Transaction $multiTransaction */
        $multiTransaction = $this->getFixtureEntityByIdent('transactionMultipleLoanersMultipleDebtors');
        /** @var Transaction $singleTransaction */
        $singleTransaction = $this->getFixtureEntityByIdent('singleTransactionHigh3');

        $this->exchangeProcessor->exchangeTransactionParts($debt7, $loan7_2);

        // afterwards single Transaction should be killed and multi decreased by 5
        // check transactions
        $this->assertEquals(150.0, $multiTransaction->getAmount()); // before 90 minus 20 (value of debt2)
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $multiTransaction->getState()); // partial cleared now
        $this->assertEquals(270.0, $singleTransaction->getAmount()); // before 300 minus 20 (part of debt2)
        $this->assertEquals(Transaction::STATE_ACCEPTED, $singleTransaction->getState()); // should be cleared

        // check transactionParts
        $this->assertEquals(40.0, $debt7->getAmount()); // before 20 minus 300 (value of loan2_3)
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt7->getState()); // should be accepted further
        $this->assertEquals(270.0, $loan7_2->getAmount()); // before 300 minus 20 (part of loan6)
        $this->assertEquals(Transaction::STATE_ACCEPTED, $loan7_2->getState()); // should be accepted further
        $this->assertEquals(0.0, $loan4->getAmount()); // before 90 minus 20 = 70
        $this->assertEquals(Transaction::STATE_CLEARED, $loan4->getState()); // should be cleared
        $this->assertEquals(270.0, $debt4_2->getAmount()); // before 300 minus 20 = 280
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt4_2->getState()); // should be cleared

        // check exchange creation
    }

    /**
     * HIGH = cmulti | LOW = multi
     * D-L > 1-L
     */
    public function testCalculateExchangeCaseHighCompleteMultiAndLowMultiTransactionParts(): void
    {
        // transaction1: user2,3,4 => 90€ => user1
        // transaction2: user1 => 180€ => user5,6,7
        // transaction3: user5,6,7 => 180€ => user2,3,4
        // transaction4: user5 => 5€ => user1
        // transaction4: user1 => 5€ => user2
        // transaction5: user6 => 200€ => user1
        // transaction6: user1 => 300€ => user2
        // transaction7: user2 => 5€ => user6
        // transaction8: user4 => 300€ => user7
        // we will exchange debt7 (multi 180€ | 70€ part of user7) with loan7_2 (single 300€)

        // parts gave into function
        /** @var Debt $debt1 */
        $debt1 = $this->getFixtureEntityByIdent('debt1');
        /** @var Loan $loan1_4 */
        $loan1_4 = $this->getFixtureEntityByIdent('loan1_4');

        // parts affected
        /** @var Loan $loan7 */
        $loan7 = $this->getFixtureEntityByIdent('loan7');
        /** @var Debt $debt7_2 */
        $debt7_2 = $this->getFixtureEntityByIdent('debt7_2');

        /** @var Transaction $completeMultiTransaction */
        $completeMultiTransaction = $this->getFixtureEntityByIdent('transactionMultipleLoanersMultipleDebtors2');
        /** @var Transaction $multipleLoanerTransaction */
        $multipleLoanerTransaction = $this->getFixtureEntityByIdent('transactionMultipleLoanersSingleDebtor');

        $this->exchangeProcessor->exchangeTransactionParts($debt1, $loan1_4);

        // afterwards single Transaction should be killed and multi decreased by 5
        // check transactions => both multiple transactions are only partial cleared
        $this->assertEquals(80.0, $completeMultiTransaction->getAmount()); // before 90 minus 20 (value of debt2)
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $completeMultiTransaction->getState()); // partial cleared now
        $this->assertEquals(130.0, $multipleLoanerTransaction->getAmount()); // before 300 minus 20 (part of debt2)
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $multipleLoanerTransaction->getState()); // should be cleared

        // check transactionParts
        $this->assertEquals(130.0, $debt1->getAmount()); // before 20 minus 300 (value of loan2_3)
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt1->getState()); // should be accepted further
        $this->assertEquals(0.0, $loan1_4->getAmount()); // before 300 minus 20 (part of loan6)
        $this->assertEquals(Transaction::STATE_CLEARED, $loan1_4->getState()); // should be accepted further
        $this->assertEquals(20.0, $loan7->getAmount()); // before 90 minus 20 = 70
        $this->assertEquals(Transaction::STATE_ACCEPTED, $loan7->getState()); // should be cleared
        $this->assertEquals(10.0, $debt7_2->getAmount()); // before 300 minus 20 = 280
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt7_2->getState()); // should be cleared
    }

    /**
     * HIGH = multi | LOW = cmulti
     * D-L < 1-L
     */
    public function testCalculateExchangeCaseHighMultiAndLowCompleteMultiTransactionParts(): void
    {
        // transaction1: user2,3,4 => 90€ => user1
        // transaction2: user1 => 180€ => user5,6,7
        // transaction3: user5,6,7 => 180€ => user2,3,4
        // transaction4: user5 => 5€ => user1
        // transaction4: user1 => 5€ => user2
        // transaction5: user6 => 200€ => user1
        // transaction6: user1 => 300€ => user2
        // transaction7: user2 => 5€ => user6
        // transaction8: user4 => 300€ => user7
        // we will exchange debt7 (multi 180€ | 70€ part of user7) with loan7_2 (single 300€)

        // parts gave into function
        /** @var Debt $debt1 */
        $debt1 = $this->getFixtureEntityByIdent('debt1');
        /** @var Loan $loan1_5 */
        $loan1_5 = $this->getFixtureEntityByIdent('loan1_5');

        // parts affected
        /** @var Loan $loan7 */
        $loan7 = $this->getFixtureEntityByIdent('loan7');
        /** @var Debt $debt7_3 */
        $debt7_3 = $this->getFixtureEntityByIdent('debt7_3');

        /** @var Transaction $completeMultiTransaction */
        $completeMultiTransaction = $this->getFixtureEntityByIdent('transactionMultipleLoanersMultipleDebtors3');
        /** @var Transaction $multipleLoanerTransaction */
        $multipleLoanerTransaction = $this->getFixtureEntityByIdent('transactionMultipleLoanersSingleDebtor');

        $this->exchangeProcessor->exchangeTransactionParts($debt1, $loan1_5);

        // afterwards single Transaction should be killed and multi decreased by 5
        // check transactions => both multiple transactions are only partial cleared
        $this->assertEquals(210.0, $completeMultiTransaction->getAmount()); // before 90 minus 20 (value of debt2)
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $completeMultiTransaction->getState()); // partial cleared now
        $this->assertEquals(110.0, $multipleLoanerTransaction->getAmount()); // before 300 minus 20 (part of debt2)
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $multipleLoanerTransaction->getState()); // should be cleared

        // check transactionParts
        $this->assertEquals(110.0, $debt1->getAmount()); // before 20 minus 300 (value of loan2_3)
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt1->getState()); // should be accepted further
        $this->assertEquals(130.0, $loan1_5->getAmount()); // before 300 minus 20 (part of loan6)
        $this->assertEquals(Transaction::STATE_ACCEPTED, $loan1_5->getState()); // should be accepted further
        $this->assertEquals(0.0, $loan7->getAmount()); // before 90 minus 20 = 70
        $this->assertEquals(Transaction::STATE_CLEARED, $loan7->getState()); // should be cleared
        $this->assertEquals(50.0, $debt7_3->getAmount()); // before 300 minus 20 = 280
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt7_3->getState()); // should be cleared
    }

    /**
     * HIGH = multi | LOW = cmulti
     * D-L < D-L
     */
    public function testCalculateExchangeCaseHighCompleteMultiAndLowCompleteMultiTransactionParts(): void
    {
        // transaction1: user2,3,4 => 90€ => user1
        // transaction2: user1 => 180€ => user5,6,7
        // transaction3: user5,6,7 => 180€ => user2,3,4
        // transaction4: user5 => 5€ => user1
        // transaction4: user1 => 5€ => user2
        // transaction5: user6 => 200€ => user1
        // transaction6: user1 => 300€ => user2
        // transaction7: user2 => 5€ => user6
        // transaction8: user4 => 300€ => user7
        // we will exchange debt7 (multi 180€ | 70€ part of user7) with loan7_2 (single 300€)

        // parts gave into function
        /** @var Debt $debt6 */
        $debt6 = $this->getFixtureEntityByIdent('debt6');
        /** @var Loan $loan6_4 */
        $loan6_4 = $this->getFixtureEntityByIdent('loan6_4');

        // parts affected (first candidate)
        /** @var Loan $loan2 */
        $loan2 = $this->getFixtureEntityByIdent('loan2');
        /** @var Debt $debt2_4 */
        $debt2_4 = $this->getFixtureEntityByIdent('debt2_4');

        // parts not affected (second candidate)
        /** @var Loan $loan3 */
        $loan3 = $this->getFixtureEntityByIdent('loan3');
        /** @var Debt $debt3_3 */
        $debt3_3 = $this->getFixtureEntityByIdent('debt3_3');

        /** @var Transaction $completeMultiTransactionLeft */
        $completeMultiTransactionLeft = $this->getFixtureEntityByIdent('transactionMultipleLoanersMultipleDebtors3');
        /** @var Transaction $completeMultipleLoanerTransactionRight */
        $completeMultipleLoanerTransactionRight = $this->getFixtureEntityByIdent('transactionMultipleLoanersMultipleDebtors');

        $this->exchangeProcessor->exchangeTransactionParts($debt6, $loan6_4);

        // afterwards single Transaction should be killed and multi decreased by 5
        // check transactions => both multiple transactions are only partial cleared
        $this->assertEquals(260.0, $completeMultiTransactionLeft->getAmount()); // before 90 minus 20 (value of debt2)
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $completeMultiTransactionLeft->getState()); // partial cleared now
        $this->assertEquals(160.0, $completeMultipleLoanerTransactionRight->getAmount()); // before 300 minus 20 (part of debt2)
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $completeMultipleLoanerTransactionRight->getState()); // should be cleared

        // check transactionParts
        $this->assertEquals(40.0, $debt6->getAmount()); // before 20 minus 300 (value of loan2_3)
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt6->getState()); // should be accepted further
        $this->assertEquals(0.0, $loan6_4->getAmount()); // before 300 minus 20 (part of loan6)
        $this->assertEquals(Transaction::STATE_CLEARED, $loan6_4->getState()); // should be accepted further
        $this->assertEquals(80.0, $loan2->getAmount()); // before 90 minus 20 = 70
        $this->assertEquals(Transaction::STATE_ACCEPTED, $loan2->getState()); // should be cleared
        $this->assertEquals(80.0, $debt2_4->getAmount()); // before 300 minus 20 = 280
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt2_4->getState()); // should be cleared

        // second candidate should not be affected
        $this->assertEquals(50.0, $loan3->getAmount()); // before 90 minus 20 = 70
        $this->assertEquals(Transaction::STATE_ACCEPTED, $loan3->getState()); // should be cleared
        $this->assertEquals(60.0, $debt3_3->getAmount()); // before 300 minus 20 = 280
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt3_3->getState()); // should be cleared
    }

    /**
     * HIGH = multi | LOW = cmulti
     * D-L > D-L
     */
    public function testCalculateExchangeCaseHighCompleteMultiAndLowCompleteMultiTransactionParts2(): void
    {
        // transaction1: user2,3,4 => 90€ => user1
        // transaction2: user1 => 180€ => user5,6,7
        // transaction3: user5,6,7 => 180€ => user2,3,4
        // transaction4: user5 => 5€ => user1
        // transaction4: user1 => 5€ => user2
        // transaction5: user6 => 200€ => user1
        // transaction6: user1 => 300€ => user2
        // transaction7: user2 => 5€ => user6
        // transaction8: user4 => 300€ => user7
        // we will exchange debt7 (multi 180€ | 70€ part of user7) with loan7_2 (single 300€)

        // parts gave into function
        /** @var Debt $debt5 */
        $debt5 = $this->getFixtureEntityByIdent('debt5');
        /** @var Loan $loan5_3 */
        $loan5_3 = $this->getFixtureEntityByIdent('loan5_3');

        // parts affected (first candidate)
        /** @var Loan $loan2 */
        $loan2 = $this->getFixtureEntityByIdent('loan2');
        /** @var Debt $debt2_4 */
        $debt2_4 = $this->getFixtureEntityByIdent('debt2_4');

        // parts not affected (second candidate)
        /** @var Loan $loan3 */
        $loan3 = $this->getFixtureEntityByIdent('loan3');
        /** @var Debt $debt3_3 */
        $debt3_3 = $this->getFixtureEntityByIdent('debt3_3');

        /** @var Transaction $completeMultiTransactionLeft */
        $completeMultiTransactionLeft = $this->getFixtureEntityByIdent('transactionMultipleLoanersMultipleDebtors3');
        /** @var Transaction $completeMultipleLoanerTransactionRight */
        $completeMultipleLoanerTransactionRight = $this->getFixtureEntityByIdent('transactionMultipleLoanersMultipleDebtors');

        $this->exchangeProcessor->exchangeTransactionParts($debt5, $loan5_3);

        // afterwards single Transaction should be killed and multi decreased by 5
        // check transactions => both multiple transactions are only partial cleared
        $this->assertEquals(230.0, $completeMultiTransactionLeft->getAmount()); // before 90 minus 20 (value of debt2)
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $completeMultiTransactionLeft->getState()); // partial cleared now
        $this->assertEquals(130.0, $completeMultipleLoanerTransactionRight->getAmount()); // before 300 minus 20 (part of debt2)
        $this->assertEquals(Transaction::STATE_PARTIAL_CLEARED, $completeMultipleLoanerTransactionRight->getState()); // should be cleared

        // check transactionParts
        $this->assertEquals(0.0, $debt5->getAmount()); // before 20 minus 300 (value of loan2_3)
        $this->assertEquals(Transaction::STATE_CLEARED, $debt5->getState()); // should be accepted further
        $this->assertEquals(10.0, $loan5_3->getAmount()); // before 300 minus 20 (part of loan6)
        $this->assertEquals(Transaction::STATE_ACCEPTED, $loan5_3->getState()); // should be accepted further
        $this->assertEquals(50.0, $loan2->getAmount()); // before 90 minus 20 = 70
        $this->assertEquals(Transaction::STATE_ACCEPTED, $loan2->getState()); // should be cleared
        $this->assertEquals(50.0, $debt2_4->getAmount()); // before 300 minus 20 = 280
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt2_4->getState()); // should be cleared

        // second candidate should not be affected
        $this->assertEquals(50.0, $loan3->getAmount()); // before 90 minus 20 = 70
        $this->assertEquals(Transaction::STATE_ACCEPTED, $loan3->getState()); // should be cleared
        $this->assertEquals(60.0, $debt3_3->getAmount()); // before 300 minus 20 = 280
        $this->assertEquals(Transaction::STATE_ACCEPTED, $debt3_3->getState()); // should be cleared
    }
}
