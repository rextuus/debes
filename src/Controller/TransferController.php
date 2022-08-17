<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\PaymentAction;
use App\Entity\PaypalAccount;
use App\Entity\Transaction;
use App\Entity\User;
use App\Form\ChoiceType;
use App\Form\ExchangeType;
use App\Form\PrepareTransferType;
use App\Service\Debt\DebtDto;
use App\Service\Mailer\MailService;
use App\Service\PaymentAction\PaymentActionData;
use App\Service\PaymentAction\PaymentActionService;
use App\Service\PaymentOption\BankAccountService;
use App\Service\PaymentOption\PaypalAccountService;
use App\Service\Transaction\DtoProvider;
use App\Service\Transaction\TransactionProcessor;
use App\Service\Transaction\TransactionService;
use App\Service\Transaction\TransactionUpdateData;
use App\Service\Transfer\ExchangeProcessor;
use App\Service\Transfer\PrepareExchangeTransferData;
use App\Service\Transfer\PrepareTransferData;
use App\Service\Transfer\SendTransferDto;
use App\Service\Transfer\TransferService;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class PaymentController
 * @IsGranted("ROLE_USER")
 * @Route("/transfer")
 */
class TransferController extends AbstractController
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
     * @var PaymentActionService
     */
    private $paymentActionService;

    /**
     * @var TransactionProcessor
     */
    private $transactionProcessor;

    /**
     * @var DtoProvider
     */
    private $dtoProvider;

    /**
     * TransactionController constructor.
     */
    public function __construct(
        TransactionService $transactionService,
        MailService $mailService,
        PaymentActionService $paymentActionService,
        TransactionProcessor $transactionProcessor,
        DtoProvider $dtoProvider
    ) {
        $this->transactionService = $transactionService;
        $this->mailService = $mailService;
        $this->paymentActionService = $paymentActionService;
        $this->transactionProcessor = $transactionProcessor;
        $this->dtoProvider = $dtoProvider;
    }

    /**
     * @Route("/prepare/bank/{slug}", name="transfer_prepare")
     * @throws Exception
     */
    public function prepareTransfer(
        Transaction $transaction,
        Request $request,
        TransferService $transferService
    ): Response {
        /** @var User $requester */
        $requester = $this->getUser();

        $isDebtor = $this->transactionService->checkRequestForVariant(
            $requester,
            $transaction,
            TransactionService::DEBTOR_VIEW,
            Transaction::STATE_ACCEPTED
        );

        $data = (new PrepareTransferData());
        $default = $transferService->getDefaultPaymentOptionForUser($requester);
        if (!$default) {
            throw new Exception('user has no payment option defined or enabled');
        }
        $data->setPaymentOption($default);

        $form = $this->createForm(
            PrepareTransferType::class,
            $data,
            ['label' => ['transaction' => $transferService->getAvailablePaymentMethodsForTransaction($transaction)]]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isDeclined = (bool)$form->get('decline')->isClicked();
            if ($isDeclined) {
                return $this->redirectToRoute('account_debts', []);
            }

            /** @var PrepareTransferData $data */
            $data = $form->getData();
            if ($data->getPaymentOption() instanceof BankAccount) {
                return $this->redirectToRoute('transfer_send_bank', [
                    'slug'              => $transaction->getSlug(),
                    'senderBankAccount' => $data->getPaymentOption()->getId(),
                ]);
            } elseif ($data->getPaymentOption() instanceof PaypalAccount) {
                return $this->redirectToRoute('transfer_send_paypal', [
                    'slug'                => $transaction->getSlug(),
                    'senderPaypalAccount' => $data->getPaymentOption()->getId(),
                ]);
            }
        }

        $dto = $this->transactionService->createDtoFromTransaction($transaction, $isDebtor);

        return $this->render('transfer/prepare.html.twig', [
            'form' => $form->createView(),
            'dto'  => $dto,
            'debtVariant' => $isDebtor,
        ]);
    }

    /**
     * @Route("/send/{slug}/{senderBankAccount}", name="transfer_send_bank")
     */
    public function sendTransferBank(
        Transaction $transaction,
        BankAccount $senderBankAccount,
        Request $request,
        BankAccountService $bankAccountService
    ): Response {
        /** @var User $requester */
        $requester = $this->getUser();

        $this->transactionService->checkRequestForVariant(
            $requester,
            $transaction,
            TransactionService::DEBTOR_VIEW,
            Transaction::STATE_ACCEPTED
        );

        // todo check if Transaction has correct state if multiple
        $debt = $this->transactionService->getDebtPartOfUserForTransaction($transaction, $requester);


        $receiverBankAccount = $bankAccountService->getActiveBankAccountForUser($transaction->getLoans()[0]->getOwner());

        $dto = (new SendTransferDto)->initFrom($receiverBankAccount);
        $dto->setAmount($transaction->getLoans()[0]->getAmount());
        $dto->setReason($transaction->getReason());
        $dto->setTransactionId($transaction->getId());

        $labels = ['label' => ['submit' => 'Erledigt', 'decline' => 'Abbrechen']];
        $form = $this->createForm(ChoiceType::class, null, $labels);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isAccepted = (bool)$form->get('submit')->isClicked();

            if ($isAccepted) {
                $transactionUpdateData = (new TransactionUpdateData())->initFrom($transaction);
                $transactionUpdateData->setState(Transaction::STATE_CLEARED);
                $this->transactionService->update($transaction, $transactionUpdateData);
                $this->transactionProcessor->process($debt);

                // store payment for history
                $paymentActionData = new PaymentActionData();
                $paymentActionData->setTransaction($transaction);
                $paymentActionData->setVariant(PaymentAction::VARIANT_BANK);
                $paymentActionData->setBankAccountSender($senderBankAccount);
                $paymentActionData->setBankAccountReceiver($receiverBankAccount);
                $paymentAction = $this->paymentActionService->storePaymentAction($paymentActionData);

                $this->mailService->sendNotificationMail($transaction, MailService::MAIL_DEBT_PAYED_ACCOUNT,
                                                             $paymentAction);
            }
            return $this->redirectToRoute('account_debts', []);
        }

        return $this->render('transfer/send.bank.html.twig', [
            'dto'  => $dto,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/send/{slug}/{senderPaypalAccount}", name="transfer_send_paypal")
     */
    public function sendTransferPaypal(
        Transaction $transaction,
        PaypalAccount $senderPaypalAccount,
        Request $request,
        PaypalAccountService $paypalAccountService
    ): Response {
        /** @var User $requester */
        $requester = $this->getUser();

        $this->transactionService->checkRequestForVariant(
            $requester,
            $transaction,
            TransactionService::DEBTOR_VIEW,
            Transaction::STATE_ACCEPTED
        );

        $receiverPaypalAccount = $paypalAccountService->getPaypalAccountForUser($transaction->getLoans()[0]->getOwner());

        $dto = (new SendTransferDto)->initFrom($receiverPaypalAccount);
        $dto->setAmount($transaction->getLoans()[0]->getAmount());
        $dto->setReason($transaction->getReason());
        $dto->setTransactionId($transaction->getId());

        $labels = ['label' => ['submit' => 'Erledigt', 'decline' => 'Abbrechen']];
        $form = $this->createForm(ChoiceType::class, null, $labels);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isAccepted = (bool)$form->get('submit')->isClicked();

            if ($isAccepted) {
                $transactionUpdateData = (new TransactionUpdateData())->initFrom($transaction);
                $transactionUpdateData->setState(Transaction::STATE_CLEARED);
                $this->transactionService->update($transaction, $transactionUpdateData);

                $paymentActionData = new PaymentActionData();
                $paymentActionData->setTransaction($transaction);
                $paymentActionData->setVariant(PaymentAction::VARIANT_PAYPAL);
                $paymentActionData->setPaypalAccountSender($senderPaypalAccount);
                $paymentActionData->setPaypalAccountReceiver($receiverPaypalAccount);
                $paymentAction = $this->paymentActionService->storePaymentAction($paymentActionData);

                $this->mailService->sendTransferMailToLoaner($transaction, MailService::MAIL_DEBT_PAYED_PAYPAL,
                                                             $paymentAction);
            }
            return $this->redirectToRoute('account_debts', []);
        }

        return $this->render('transfer/send.paypal.html.twig', [
            'dto'  => $dto,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/prepare/exchange/{slug}", name="exchange_prepare")
     */
    public function prepareExchange(
        Transaction $transaction,
        Request $request,
        ExchangeProcessor $exchangeProcessor
    ): Response {
        /** @var User $requester */
        $requester = $this->getUser();

        $this->transactionService->checkRequestForVariant(
            $requester,
            $transaction,
            TransactionService::DEBTOR_VIEW,
            Transaction::STATE_ACCEPTED
        );

        $debt = $this->transactionService->getDebtPartOfUserForTransaction($transaction, $requester);
        $candidates = $exchangeProcessor->findExchangeCandidatesForTransactionPart($debt);

        if (empty($candidates->getAllCandidates())){
            $this->addFlash('success', 'Article Created! Knowledge is power!');
            return $this->redirectToRoute('account_debts', []);
        }

        $data = new PrepareExchangeTransferData();
        $form = $this->createForm(
            ExchangeType::class,
            $data,
            ['debt' => $debt]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isAccepted = (bool)$form->get('submit')->isClicked();
            if ($isAccepted) {
                if (!$data->getLoan()) {
                    return $this->redirectToRoute('account_debts', []);
                }

                /** @var PrepareExchangeTransferData $data */
                $data = $form->getData();
                $loanToExchange = $data->getLoan();
                return $this->redirectToRoute(
                    'exchange_accept',
                    [
//                        'slug1' => $transaction->getSlug(),
//                        'slug2' => $loanToExchange->getTransaction()->getSlug(),
                        'debt_id' => $this->transactionService->getDebtPartOfUserForTransaction($transaction,
                                                                                              $requester)->getId(),
                        'loan_id' => $loanToExchange->getId(),
                    ]
                );
            }
            return $this->redirectToRoute('account_debts', []);
        }

        $dto = DebtDto::create($this->transactionService->getDebtPartOfUserForTransaction($transaction, $requester));

        return $this->render('transfer/prepare.exchange.html.twig', [
            'dto'  => $dto,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/accept/exchange/{debt_id}/{loan_id}", name="exchange_accept")
     * @ParamConverter("debt", options={"id" = "debt_id"})
     * @ParamConverter("loan", options={"id" = "loan_id"})
     */
    public function acceptExchange(
        Debt              $debt,
        Loan              $loan,
        Request           $request,
        ExchangeProcessor $exchangeService
    ): Response {
        $dto = $exchangeService->calculateExchange($debt->getTransaction(), $loan->getTransaction());
        $labels = ['label' => ['submit' => 'Verrechnen', 'decline' => 'Zurück zur Auswahl']];
        $form = $this->createForm(ChoiceType::class, null, $labels);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isAccepted = (bool)$form->get('submit')->isClicked();

            if ($isAccepted) {
                $exchangeService->exchangeTransactionParts($debt, $loan);
                return $this->redirectToRoute('account_debts', []);
            } else {
                return $this->redirectToRoute('exchange_prepare', ['slug' => $debt]);
            }
        }

        return $this->render('transfer/accept.exchange.html.twig', [
            'form' => $form->createView(),
            'dto'  => $dto,
        ]);
    }
}
