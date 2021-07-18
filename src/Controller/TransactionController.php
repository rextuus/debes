<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\User;
use App\Form\ChoiceType;
use App\Form\TransactionCreateDebtorType;
use App\Form\TransactionCreateSimpleType;
use App\Form\TransactionCreateType;
use App\Repository\TransactionRepository;
use App\Service\Debt\DebtDto;
use App\Service\Loan\LoanDto;
use App\Service\Mailer\MailService;
use App\Service\Transaction\TransactionCreateData;
use App\Service\Transaction\TransactionCreateDebtorData;
use App\Service\Transaction\TransactionData;
use App\Service\Transaction\TransactionService;
use Exception;
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
     * @var TransactionService
     */
    private $transactionService;

    /**
     * @var MailService
     */
    private $mailService;

    /**
     * TransactionController constructor.
     */
    public function __construct(TransactionService $transactionService, MailService $mailService)
    {
        $this->transactionService = $transactionService;
        $this->mailService = $mailService;
    }

    /**
     * @Route("/create/simple", name="transaction_create_simple")
     */
    public function createSimpleTransaction(Request $request): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $transactionData = (new TransactionCreateData());
        $form = $this->createForm(TransactionCreateSimpleType::class, $transactionData, ['requester' => $requester]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var TransactionData $data */
            $data = $form->getData();

            $transaction = $this->transactionService->storeSimpleTransaction($data, $requester);

            $this->mailService->sendCreationMail($transaction, $requester, $data->getOwner());

            return $this->redirect($this->generateUrl('account_overview', []));
        }

        return $this->render('transaction/transaction.create.simple.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/", name="transaction_list")
     */
    public function listTransactionsForUser(): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $transactions = $this->transactionService->getAllTransactionBelongingUser($requester);

        return $this->render('transaction/transaction.list.html.twig', [
            'debtAmount' => 345.77,
            'loanAmount' => 666.77,
            'transactions' => $transactions,
        ]);
    }

    /**
     * @Route("/accept/{slug}", name="transaction_accept")
     */
    public function acceptTransaction(Transaction $transaction, Request $request): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $isDebtor = $this->transactionService->checkRequestForVariant(
            $requester,
            $transaction,
            $request->get('variant'),
            Transaction::STATE_READY
        );

        if ($isDebtor) {
            $dto = DebtDto::create($transaction);
            $labels = ['label' => ['submit' => 'akzeptieren', 'decline' => 'ablehnen']];
        } else {
            $dto = LoanDto::create($transaction);
            $labels = ['label' => ['submit' => 'Zurückziehen', 'decline' => 'Zurückziehen']];
        }

        $form = $this->createForm(ChoiceType::class, null, $labels);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isAccepted = (bool)$form->get('submit')->isClicked();

            if ($isDebtor) {
                if ($isAccepted) {
                    $this->transactionService->acceptTransaction($transaction);
                    $this->mailService->sendAcceptMail($transaction, $requester, $transaction->getLoaner());
                } else {
                    $this->transactionService->declineTransaction($transaction);
                    $this->mailService->sendDeclineMail($transaction, $requester, $transaction->getLoaner());
                }
                return $this->redirectToRoute('account_debts', []);
            } else {
                if ($isAccepted) {
                    // TODO remove Transaction and send loaner notification
                }
                return $this->redirectToRoute('account_loans', []);
            }
        }

        return $this->render('transaction/transaction.accept.html.twig', [
            'debtVariant' => $isDebtor,
            'dto' => $dto,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/process/{slug}", name="transaction_process")
     */
    public function processTransaction(Transaction $transaction, Request $request): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $isDebtor = $this->transactionService->checkRequestForVariant(
            $requester,
            $transaction,
            $request->get('variant'),
            Transaction::STATE_ACCEPTED
        );

        if ($isDebtor) {
            $dto = DebtDto::create($transaction);
            $labels = ['label' => ['submit' => 'Überweisen', 'decline' => 'Verrechnen']];
        } else {
            $dto = LoanDto::create($transaction);
            $labels = ['label' => ['submit' => 'Mahn-Mail senden', 'decline' => 'Mahn-Mail senden']];
        }

        $form = $this->createForm(ChoiceType::class, null, $labels);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $useTransaction = (bool)$form->get('submit')->isClicked();
            $useChange = (bool)$form->get('decline')->isClicked();

            if ($isDebtor) {
                if ($useTransaction) {
                    return $this->redirect($this->generateUrl('transfer_prepare',
                                                              ['slug' => $transaction->getSlug()]));
                }
                if ($useChange){
                    return $this->redirect($this->generateUrl('exchange_prepare',
                                                              ['slug' => $transaction->getSlug()]));
                }
                return $this->redirectToRoute('account_debts', []);
            } else {
                if ($useTransaction) {
                    // TODO remove Transaction and send loaner notification
                }
                return $this->redirectToRoute('account_loans', []);
            }
        }

        return $this->render('transaction/transaction.process.html.twig', [
            'debtVariant' => $isDebtor,
            'dto' => $dto,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/confirm/{slug}", name="transaction_confirm")
     */
    public function confirmTransaction(Transaction $transaction, Request $request): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $isDebtor = $this->transactionService->checkRequestForVariant(
            $requester,
            $transaction,
            $request->get('variant'),
            Transaction::STATE_CLEARED
        );

        if ($isDebtor) {
            $dto = DebtDto::create($transaction);
            $labels = ['label' => ['submit' => 'Bestätigen', 'decline' => 'Bestätigen']];
        } else {
            $dto = LoanDto::create($transaction);
            $labels = ['label' => ['submit' => 'Bestätigen', 'decline' => 'Bemängeln']];
        }

        $form = $this->createForm(ChoiceType::class, null, $labels);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isAccepted = (bool)$form->get('submit')->isClicked();

            if ($isDebtor) {
                if ($isAccepted) {
                    // TODO
                } else {
                    // TODO send loaner notification to remind him that transaction was succeeded
                }
                return $this->redirectToRoute('account_debts', []);
            } else {
                if ($isAccepted) {
                    // TODO set transaction to history state and inform debtor that all is fine
                }
                return $this->redirectToRoute('account_loans', []);
            }
        }

        return $this->render('transaction/transaction.confirm.html.twig', [
            'debtVariant' => $isDebtor,
            'dto' => $dto,
            'form' => $form->createView(),
        ]);
    }

    // TODO edit is only needed for admin area. Transaction with multiple users will be a new feature in future

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
    public function createTransaction(
        Request $request
    ): Response {
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
    public function createTransactionDebtors(
        Request $request,
        TransactionService $transactionService
    ): Response {
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
