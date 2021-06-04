<?php

namespace App\Service\Transfer;

use App\Entity\PaymentOption;
use App\Entity\Transaction;
use App\Entity\User;
use App\Service\PaymentOption\PaymentOptionService;
use Exception;

/**
 * TransferService
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class TransferService
{
    /**
     * @var PaymentOptionService
     */
    private $paymentOptionService;

    /**
     * TransferService constructor.
     */
    public function __construct(PaymentOptionService $paymentOptionService)
    {
        $this->paymentOptionService = $paymentOptionService;
    }

    /**
     * getDefaultPaymentOptionForUser
     * @param User $user
     *
     * @return PaymentOption
     */
    public function getDefaultPaymentOptionForUser(User $user): PaymentOption
    {
        return $this->paymentOptionService->getDefaultPaymentOptionForUser($user);
    }

    /**
     * prepareOptions
     *
     * @param Transaction $transaction
     *
     * @return array
     * @throws Exception
     */
    public function getAvailablePaymentMethodsForTransaction(Transaction $transaction): array
    {
        $debtor = $transaction->getDebts()[0]->getOwner();
        $loaner = $transaction->getLoans()[0]->getOwner();

        $includeBank = !empty($this->paymentOptionService->getActivePaymentOptionsOfUser(
                $debtor,
                true,
                false
            ))
            && !empty($this->paymentOptionService->getActivePaymentOptionsOfUser(
                $loaner,
                true,
                false
            ));
        $includePaypal = !empty($this->paymentOptionService->getActivePaymentOptionsOfUser(
                $debtor,
                false))
            && !empty($this->paymentOptionService->getActivePaymentOptionsOfUser(
                $loaner,
                false)
            );

        if (!$includeBank && !$includePaypal){
            throw new Exception('There are no matching payment methods for both users');
        }

        $candidates = $this->paymentOptionService->getActivePaymentOptionsOfUser($debtor, $includeBank, $includePaypal);
        $choices = array();
        foreach ($candidates as $candidate) {
            /** @var PaymentOption $candidate */
            $choices[$candidate->getDescription()] = $candidate;
        }
        return $choices;
    }
}