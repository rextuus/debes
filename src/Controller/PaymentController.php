<?php

namespace App\Controller;

use App\Entity\BankAccount;
use App\Entity\PaypalAccount;
use App\Entity\User;
use App\Form\BankAccountCreateType;
use App\Form\BankAccountUpdateType;
use App\Form\PaypalAccountCreateType;
use App\Form\PaypalAccountUpdateType;
use App\Service\PaymentOption\BankAccountData;
use App\Service\PaymentOption\BankAccountService;
use App\Service\PaymentOption\BankAccountUpdateData;
use App\Service\PaymentOption\PaypalAccountCreateData;
use App\Service\PaymentOption\PaypalAccountService;
use App\Service\PaymentOption\PaypalAccountUpdateData;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class PaymentController
 * @IsGranted("ROLE_USER")
 * @Route("/payment")
 */
class PaymentController extends AbstractController
{

    /**
     * @Route("/", name="payment_overview")
     */
    public function index(BankAccountService $bankAccountService, PaypalAccountService $paypalAccountService): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();
        $bankAccounts = $bankAccountService->getBankAccountsOfUser($requester);
        $paypalAccounts = $paypalAccountService->getPaypalAccountsOfUser($requester);
        $accounts = array_merge($bankAccounts, $paypalAccounts);

        return $this->render('payment/index.html.twig', [
            'accounts' => $accounts,
        ]);
    }

    /**
     * @Route("/create/bank", name="payment_create_bank")
     */
    public function createNewBankAccount(Request $request, BankAccountService $bankAccountService): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $bankAccountData = (new BankAccountData())->initFromUser($requester);
        $form = $this->createForm(BankAccountCreateType::class, $bankAccountData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BankAccountData $data */
            $data = $form->getData();

            $bankAccountService->storeBankAccount($data);

            return $this->redirect($this->generateUrl('payment_overview'));
        }

        return $this->render('payment/bank.create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/bank/{id}", name="payment_update_bank")
     */
    public function updateBankAccount(
        BankAccount $bankAccount,
        Request $request,
        BankAccountService $bankAccountService
    ): Response {
        $bankAccountData = (new BankAccountUpdateData())->initFromEntity($bankAccount);
        $form = $this->createForm(BankAccountUpdateType::class, $bankAccountData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var BankAccountUpdateData $data */
            $data = $form->getData();

            $bankAccountService->update($bankAccount, $data);

            return $this->redirect($this->generateUrl('payment_overview'));
        }

        return $this->render('payment/bank.edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/paypal/bank", name="payment_create_paypal")
     */
    public function createNewPaypalAccount(Request $request, PaypalAccountService $paypalAccountService): Response
    {
        /** @var User $requester */
        $requester = $this->getUser();

        $paypalAccountData = (new PaypalAccountCreateData())->initFromUser($requester);
        $form = $this->createForm(PaypalAccountCreateType::class, $paypalAccountData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PaypalAccountCreateData $data */
            $data = $form->getData();

            $paypalAccountService->storePaypalAccount($data);

            return $this->redirect($this->generateUrl('payment_overview'));
        }

        return $this->render('payment/paypal.create.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/edit/paypal/{id}", name="payment_update_paypal")
     */
    public function updatePaypalAccount(
        PaypalAccount $paypalAccount,
        Request $request,
        PaypalAccountService $paypalAccountService
    ): Response {
        $bankAccountData = (new PaypalAccountUpdateData())->initFromEntity($paypalAccount);
        $form = $this->createForm(PaypalAccountUpdateType::class, $bankAccountData);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var PaypalAccountUpdateData $data */
            $data = $form->getData();

            $paypalAccountService->update($paypalAccount, $data);

            return $this->redirect($this->generateUrl('payment_overview'));
        }

        return $this->render('payment/paypal.edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
