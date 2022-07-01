<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\User;
use App\Form\ChoiceType;
use App\Form\TransactionCreateDebtorType;
use App\Form\TransactionCreateMultipleType;
use App\Form\TransactionCreateSimpleType;
use App\Form\TransactionCreateType;
use App\Service\Debt\DebtCreateData;
use App\Service\Debt\DebtDto;
use App\Service\Loan\LoanDto;
use App\Service\Mailer\MailService;
use App\Service\Transaction\TransactionCreateData;
use App\Service\Transaction\TransactionCreateDebtorData;
use App\Service\Transaction\TransactionCreateMultipleData;
use App\Service\Transaction\TransactionData;
use App\Service\Transaction\TransactionProcessor;
use App\Service\Transaction\TransactionService;
use App\Service\Transaction\TransactionUpdateData;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;


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
     * @var TransactionProcessor
     */
    private $transactionProcessor;

    /**
     * TransactionController constructor.
     *
     * @param TransactionService $transactionService
     * @param MailService $mailService
     */
    public function __construct(TransactionService $transactionService, MailService $mailService, TransactionProcessor $transactionProcessor)
    {
        $this->transactionService = $transactionService;
        $this->mailService = $mailService;
        $this->transactionProcessor = $transactionProcessor;
    }

    /**
     * @Route("/create/simple", name="transaction_create_simple")
     * @param Request $request
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
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

            $transaction = $this->transactionService->storeSingleTransaction($data, $requester);

            $this->mailService->sendNotificationMail($transaction, MailService::MAIL_DEBT_CREATED);

            return $this->redirect($this->generateUrl('account_overview', []));
        }

        return $this->render('transaction/transaction.create.simple.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // TODO CHECK IF USED
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
     *
     * @param Transaction $transaction
     * @param Request $request
     * @return Response
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws Exception
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
            $debt = $this->transactionService->getDebtPartOfUserForTransaction($transaction, $requester);
            $dto = DebtDto::create($debt);
            $labels = ['label' => ['submit' => 'akzeptieren', 'decline' => 'ablehnen']];
        } else {
            $loan = $this->transactionService->getLoanPartOfUserForTransaction($transaction, $requester);
            $dto = LoanDto::create($loan);
            $labels = ['label' => ['submit' => 'Zurückziehen', 'decline' => 'Zurückziehen']];
        }

        $form = $this->createForm(ChoiceType::class, null, $labels);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isAccepted = (bool)$form->get('submit')->isClicked();

            if ($isDebtor) {
                if ($isAccepted) {
                    $this->transactionProcessor->accept($debt);
                    $this->mailService->sendNotificationMail($transaction, MailService::MAIL_DEBT_ACCEPTED);
                } else {
                    $this->transactionService->declineDebt($debt);
                    $this->mailService->sendNotificationMail($transaction, MailService::MAIL_DEBT_DECLINED);
                }
                return $this->redirectToRoute('account_debts', []);
            } else {
                if ($isAccepted) {
                    $this->mailService->sendNotificationMail($transaction, MailService::MAIL_DEBT_CANCELED);
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
            $dto = $this->transactionService->createDtoFromTransaction($transaction, true);
            $labels = ['label' => ['submit' => 'Überweisen', 'decline' => 'Verrechnen']];
        } else {
            $dto = $this->transactionService->createDtoFromTransaction($transaction, false);
            $labels = ['label' => ['submit' => 'Mahn-Mail senden', 'decline' => 'Mahn-Mail senden']];
        }
        $dto = $this->transactionService->createDtoFromTransaction($transaction, $isDebtor);

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
            $dto = DebtDto::create($this->transactionService->getDebtPartOfUserForTransaction($transaction, $requester));
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
                    $this->transactionService->confirmTransaction($transaction);
                    $this->mailService->sendNotificationMail($transaction, MailService::MAIL_DEBT_CONFIRMED, null);
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
        /** @var User $requester */
        $requester = $this->getUser();

        $transactionData = (new TransactionCreateMultipleData());
        $dat = new DebtCreateData();
        $dat->setOwner($requester);
        $transactionData->setDebtorsData([$dat]);
        $form = $this->createForm(TransactionCreateMultipleType::class, $transactionData, ['requester' => $requester]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $requester */
            $requester = $this->getUser();

            /** @var TransactionCreateMultipleData $data */
            $data = $form->getData();

            $this->transactionService->storeMultipleTransaction($data, $requester);
        }

        return $this->render('transaction/transaction.create.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
