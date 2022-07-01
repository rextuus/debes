<?php

namespace App\Tests;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Entity\User;
use App\Repository\DebtRepository;
use App\Repository\ExchangeRepository;
use App\Repository\LoanRepository;
use App\Repository\TransactionRepository;
use App\Repository\UserRepository;
use App\Service\Exchange\ExchangeService;
use App\Service\Transaction\TransactionCreateData;
use App\Service\Transaction\TransactionService;
use App\Service\Transfer\ExchangeProcessor;

/**
 * TransactionServiceTest
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class TransactionServiceTest extends FixtureTestCase
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
                __DIR__.'/transactions.yml',
                __DIR__.'/exchanges.yml'
            ]
        );
        $this->exchangeProcessor = $this->getService(TransactionService::class);
    }

    protected function tearDown(): void
    {

        parent::tearDown();
    }


    public function testCreateSimpleTransaction(): void
    {
        $transactionRepository = $this->getService(TransactionRepository::class);
        $debtRepository = $this->getService(DebtRepository::class);
        $loanRepository = $this->getService(LoanRepository::class);

        $transactionsBefore = $transactionRepository->count();
        $debtsBefore = $debtRepository->count();
        $loansBefore = $loanRepository->count();

        $amount = 19.48;

        $data = new TransactionCreateData();
        $data->setAmount($amount);
        $data->set($amount);

        $transactionsAfter = $transactionRepository->count();
        $debtsAfter = $debtRepository->count();
        $loansAfter = $loanRepository->count();

        $this->assertEquals($transactionsBefore+1, $transactionsAfter);
        $this->assertEquals($debtsBefore+1, $debtsAfter);
        $this->assertEquals($loansBefore+1, $loansAfter);
    }
}
