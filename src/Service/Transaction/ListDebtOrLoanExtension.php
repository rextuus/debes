<?php

namespace App\Service\Transaction;

use App\Entity\Transaction;
use App\Entity\User;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * ListDebtOrLoanExtension
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class ListDebtOrLoanExtension extends AbstractExtension
{
    /**
     * @var TransactionService
     */
    private $transactionService;

    /**
     * @param TransactionService $transactionService
     */
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'render_debt_or_loan_list',
                [$this, 'renderDebtOrLoanList'],
                [
                    'needs_environment' => true,
                    'is_safe' => ['html'],
                ]
            ),
        ];
    }

    /**
     * renderDebtOrLoanList
     *
     * @param Environment $environment
     * @param User        $owner
     * @param bool        $debtVariant
     *
     * @return string
     * @throws LoaderError
     * @throws RuntimeError
     * @throws SyntaxError
     */
    public function renderDebtOrLoanList(
        Environment $environment,
        User $owner,
        bool $debtVariant
    ): string {
        if ($debtVariant){
            $ready = $this->transactionService->getAllDebtTransactionsForUserAndState($owner, Transaction::STATE_READY);
            $accepted = $this->transactionService->getAllDebtTransactionsForUserAndState($owner, Transaction::STATE_ACCEPTED);
            $cleared = $this->transactionService->getAllDebtTransactionsForUserAndState($owner, Transaction::STATE_CLEARED);
        }else{
            $ready = $this->transactionService->getAllLoanTransactionsForUserAndState($owner, Transaction::STATE_READY);
            $accepted = $this->transactionService->getAllLoanTransactionsForUserAndState($owner, Transaction::STATE_ACCEPTED);
            $cleared = $this->transactionService->getAllLoanTransactionsForUserAndState($owner, Transaction::STATE_CLEARED);
        }

        return $environment->render(
            'transaction/transaction.list.extension.html.twig',
            [
                'debtVariant' => $debtVariant,
                'ready' => $ready,
                'accepted' => $accepted,
                'cleared' => $cleared,
            ]
        );
    }
}