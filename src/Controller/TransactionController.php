<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\User;
use App\Form\TransactionCreateDebtorType;
use App\Form\TransactionCreateSimpleType;
use App\Form\TransactionCreateType;
use App\Service\Transaction\DebtorsTrait;
use App\Service\Transaction\TransactionCreateData;
use App\Service\Transaction\TransactionCreateDebtorData;
use App\Service\Transaction\TransactionData;
use App\Service\Transaction\TransactionService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PaymentController
 * @IsGranted("ROLE_USER")
 * @Route("/transaction")
 */
class TransactionController extends AbstractController
{
    /**
     * @Route("/create/simple", name="transaction_create_simple")
     */
    public function createSimpleTransaction(Request $request, TransactionService $transactionService): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $transactionData = (new TransactionData());
        $form = $this->createForm(TransactionCreateSimpleType::class, $transactionData, ['requester' => $requester]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var TransactionData $data */
            $data = $form->getData();

            $transactionService->storeSimpleTransaction($data, $requester);
            return $this->redirect($this->generateUrl('transaction_overview'));
        }

        return $this->render('transaction/transaction.create.simple.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/", name="transaction_list")
     */
    public function listTransactionsForUser(TransactionService $transactionService): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $transactions = $transactionService->getAllTransactionBelongingUser($requester);

        return $this->render('transaction/transaction.list.html.twig', [
            'debtAmount' => 345.77,
            'loanAmount' => 666.77,
            'transactions' => $transactions,
        ]);
    }

    /**
     * @Route('/accept/{transaction}', name='transaction_list')
     */
    public function acceptTransaction(Transaction $transaction, TransactionService $transactionService): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $transactions = $transactionService->getAllTransactionBelongingUser($requester);

        return $this->render('transaction/transaction.list.html.twig', [
            'debtAmount' => 345.77,
            'loanAmount' => 666.77,
            'transactions' => $transactions,
        ]);
    }


    /**
     * @Route("/edit", name="transaction_edit")
     */
    public function editTransaction(): Response
    {
        return $this->render('transaction/transaction.create.html.twig', [
            'controller_name' => 'TransactionController',
        ]);
    }

























    /**
     * @Route("/create", name="transaction_create")
     */
    public function createTransaction(Request $request, TransactionService $transactionService): Response
    {
        $transactionData = (new TransactionCreateData());
        $form = $this->createForm(TransactionCreateType::class, $transactionData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $requester */
            $requester = $this->getUser();

            /** @var TransactionCreateData $data */
            $data = $form->getData();
            $data->setRequester($requester);

            $transactionData = (new TransactionCreateDebtorData())->initFromData($data);
            $form = $this->createForm(
                TransactionCreateDebtorType::class,
                $transactionData,
                [
                    'debtors' => $data->getDebtors(),
                    'requester' => $requester,
                ]
            );

            return $this->render(
                'transaction/transaction.create.details.html.twig',
                [
                    'form' => $form->createView(),
                    'numberOfDebtors' => $data->getDebtors(),
                    'debtors' => $transactionData->getDebtorData(),
                ]
            );

            return $this->redirect($this->generateUrl('transaction_overview'));
        }

        return $this->render('transaction/transaction.create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/create/debtors", name="transaction_create_debtors")
     */
    public function createTransactionDebtors(Request $request, TransactionService $transactionService): Response
    {
        $transactionData = (new TransactionCreateDebtorType());
        $form = $this->createForm(TransactionCreateDebtorType::class, $transactionData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var TransactionCreateData $data */
            $data = $form->getData();

            $transactionService->storeTransaction($data);

            return $this->redirect($this->generateUrl('transaction_overview'));
        }

        return $this->render('transaction/transaction.create.details.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
