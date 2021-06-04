<?php

namespace App\Controller;

use App\Entity\Transaction;
use App\Entity\User;
use App\Form\PrepareTransferType;
use App\Service\Debt\DebtDto;
use App\Service\PaymentOption\PaymentOptionService;
use App\Service\Transaction\TransactionService;
use App\Service\Transfer\PrepareTransferData;
use App\Service\Transfer\TransferService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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
     * @Route("/prepare/{transaction}", name="transfer_prepare")
     */
    public function prepareTransfer(Transaction $transaction, TransferService $transferService): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $data = (new PrepareTransferData());
        $default = $transferService->getDefaultPaymentOptionForUser($requester);
        if (!$default){
            throw new \Exception('user has no payment option defined or enabled');
        }
        $data->setPaymentOption($default);

        $form = $this->createForm(
            PrepareTransferType::class,
            $data,
            ['label' => ['transaction' => $transferService->getAvailablePaymentMethodsForTransaction($transaction)]]
        );

        $dto = DebtDto::create($transaction);
        return $this->render('transfer/prepare.html.twig', [
            'form' => $form->createView(),
            'dto' => $dto,
        ]);
    }
}
