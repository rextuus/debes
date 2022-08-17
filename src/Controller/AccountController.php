<?php

namespace App\Controller;

use App\Entity\User;
use App\Service\Transaction\TransactionService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;

/**
 * Class AccountController
 *
 * @IsGranted("ROLE_USER")
 *
 * @Route("/me")
 */
class AccountController extends AbstractController
{
    /**
     * @Route("/", name="account_overview")
     */
    public function listTransactionsForUser(TransactionService $transactionService): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $transactions = $transactionService->getAllTransactionBelongingUser($requester);

        $loans = $transactionService->getTotalLoansForUser($requester);
        $debts = $transactionService->getTotalDebtsForUser($requester);

        return $this->render('transaction/transaction.list.html.twig', [
            'debtAmount' => $debts,
            'loanAmount' => $loans,
            'balance' => $loans - $debts,
            'transactions' => $transactions
        ]);
    }

    /**
     * @Route("/debts", name="account_debts")
     */
    public function listDebtsForUser(TransactionService $service): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        return $this->render('transaction/transaction.list.debts.html.twig', [
            'user' => $requester
        ]);
    }

    /**
     * @Route("/loans", name="account_loans")
     */
    public function listLoansForUser(TransactionService $transactionService): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        return $this->render('transaction/transaction.list.loans.html.twig', [
            'user' => $requester
        ]);
    }
}
