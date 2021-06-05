<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\Transaction;
use App\Entity\User;
use App\Form\ChoiceType;
use App\Form\PrepareTransferType;
use App\Service\Debt\DebtDto;
use App\Service\PaymentOption\BankAccountService;
use App\Service\Transaction\TransactionService;
use App\Service\Transaction\TransactionUpdateData;
use App\Service\Transfer\PrepareTransferData;
use App\Service\Transfer\SendTransferDto;
use App\Service\Transfer\TransferService;
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
     * TransactionController constructor.
     */
    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    /**
     * @Route("/prepare/{transaction}", name="transfer_prepare")
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
            throw new \Exception('user has no payment option defined or enabled');
        }
        $data->setPaymentOption($default);

        $form = $this->createForm(
            PrepareTransferType::class,
            $data,
            ['label' => ['transaction' => $transferService->getAvailablePaymentMethodsForTransaction($transaction)]]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PrepareTransferData $data */
            $data = $form->getData();
            if ($data->getPaymentOption() instanceof BankAccount) {
                return $this->redirectToRoute('transfer_send_bank', [
                    'transaction' => $transaction->getId(),
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
     * @Route("/send/{transaction}", name="transfer_send_bank")
     */
    public function sendTransfer(
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
}
