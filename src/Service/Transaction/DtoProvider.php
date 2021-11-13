<?php

namespace App\Service\Transaction;

use App\Entity\Transaction;
use App\Service\Debt\DebtDto;
use App\Service\Exchange\ExchangeDto;
use App\Service\Exchange\ExchangeService;
use App\Service\Loan\LoanDto;

/**
 * DtoProvider
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class DtoProvider
{
    /**
     * @var ExchangeService
     */
    private $exchangeService;

    /**
     * DtoProvider constructor.
     */
    public function __construct(
        ExchangeService $exchangeService
    ) {
        $this->exchangeService = $exchangeService;
    }

    /**
     * createDebtDto
     *
     * @param Transaction $transaction
     *
     * @return DebtDto
     */
    public function createDebtDto(Transaction $transaction): DebtDto
    {
        $debtDto = DebtDto::create($transaction);
        $exchanges = $this->exchangeService->getAllExchangesBelongingToTransaction($transaction);
        $exchangeDtos = array();
        foreach ($exchanges as $exchange){
            $exchangeDtos[] = ExchangeDto::create($exchange);
        }
        $debtDto->setExchangeDtos($exchangeDtos);
        return $debtDto;
    }

    /**
     * createDebtDto
     *
     * @param Transaction $transaction
     *
     * @return LoanDto
     */
    public function createLoanDto(Transaction $transaction): LoanDto
    {
        $loanDto = LoanDto::create($transaction);
        $exchanges = $this->exchangeService->getAllExchangesBelongingToTransaction($transaction);
        $exchangeDtos = array();
        foreach ($exchanges as $exchange){
            $exchangeDtos[] = ExchangeDto::create($exchange);
        }
        $loanDto->setExchangeDtos($exchangeDtos);
        return $loanDto;
    }
}