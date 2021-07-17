<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\PaypalAccount;
use App\Entity\Transaction;
use App\Entity\User;
use App\Form\ChoiceType;
use App\Form\ExchangeType;
use App\Form\PrepareTransferType;
use App\Service\Debt\DebtDto;
use App\Service\Mailer\MailService;
use App\Service\PaymentOption\BankAccountService;
use App\Service\PaymentOption\PaypalAccountService;
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
     * TransactionController constructor.
     */
    public function __construct(TransactionService $transactionService, MailService $mailService)
    {
        $this->transactionService = $transactionService;
        $this->mailService = $mailService;
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

        $this->transactionService->checkRequestForVariant(
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
            if ($isDeclined){
                return $this->redirectToRoute('account_debts', []);
            }

            /** @var PrepareTransferData $data */
            $data = $form->getData();
            if ($data->getPaymentOption() instanceof BankAccount) {
                return $this->redirectToRoute('transfer_send_bank', [
                    'slug' => $transaction->getSlug(),
                ]);
            } elseif ($data->getPaymentOption() instanceof PaypalAccount) {
                return $this->redirectToRoute('transfer_send_paypal', [
                    'slug' => $transaction->getSlug(),
                ]);
            }
        }

        $dto = DebtDto::create($transaction);
        return $this->render('transfer/prepare.html.twig', [
            'form' => $form->createView(),
            'dto' => $dto,
        ]);
    }

    /**
     * @Route("/send/{slug}", name="transfer_send_bank")
     */
    public function sendTransferBank(
        Transaction $transaction,
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

        $bankAccount = $bankAccountService->getActiveBankAccountForUser($transaction->getLoans()[0]->getOwner());

        $dto = (new SendTransferDto)->initFrom($bankAccount);
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
            }
            return $this->redirectToRoute('account_debts', []);
        }

        return $this->render('transfer/send.bank.html.twig', [
            'dto' => $dto,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/send/{slug}", name="transfer_send_paypal")
     */
    public function sendTransferPaypal(
        Transaction $transaction,
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

        $paypalAccount = $paypalAccountService->getPaypalAccountForUser($transaction->getLoans()[0]->getOwner());

        $dto = (new SendTransferDto)->initFrom($paypalAccount);
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
            }
            return $this->redirectToRoute('account_debts', []);
        }

        return $this->render('transfer/send.paypal.html.twig', [
            'dto' => $dto,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/prepare/exchange/{slug}", name="exchange_prepare")
     */
    public function prepareExchange(
        Transaction $transaction,
        Request $request,
        ExchangeProcessor $exchangeService
    ): Response {
        /** @var User $requester */
        $requester = $this->getUser();

        $this->transactionService->checkRequestForVariant(
            $requester,
            $transaction,
            TransactionService::DEBTOR_VIEW,
            Transaction::STATE_ACCEPTED
        );

        $data = new PrepareExchangeTransferData();
        $form = $this->createForm(
            ExchangeType::class,
            $data,
            ['transaction' => $transaction]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isAccepted = (bool)$form->get('submit')->isClicked();
            if ($isAccepted){
                if (!$data->getTransactionSlug()){
                    return $this->redirectToRoute('account_debts', []);
                }
                return $this->redirectToRoute('exchange_accept', ['slug1' => $transaction->getSlug(), 'slug2' => $form->getData()->getTransactionSlug()]);
            }
            return $this->redirectToRoute('account_debts', []);
        }

        $dto = DebtDto::create($transaction);
        return $this->render('transfer/prepare.exchange.html.twig', [
            'dto' => $dto,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/accept/exchange/{slug1}/{slug2}", name="exchange_accept")
     */
    public function acceptExchange(
        string $slug1,
        string $slug2,
        Request $request,
        ExchangeProcessor $exchangeService
    ): Response {
        $dto = $exchangeService->calculateExchange($slug1, $slug2);
        $labels = ['label' => ['submit' => 'Verrechnen', 'decline' => 'Zurück zur Auswahl']];
        $form = $this->createForm(ChoiceType::class, null, $labels);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $isAccepted = (bool)$form->get('submit')->isClicked();

            if ($isAccepted){
                $exchangeService->exchangeTransactions($slug1, $slug2);
                return $this->redirectToRoute('account_debts', []);
            }else{
                return $this->redirectToRoute('exchange_prepare', ['slug' => $slug1]);
            }
        }

        return $this->render('transfer/accept.exchange.html.twig', [
            'form' => $form->createView(),
            'dto' => $dto
        ]);
    }
}
