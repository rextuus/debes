<?php

namespace App\Service\PaymentAction;

use App\Entity\PaymentAction;
use App\Repository\PaymentActionRepository;

/**
 * PaymentActionService
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class PaymentActionService
{
    /**
     * @var PaymentActionFactory
     */
    private $paymentActionFactory;

    /**
     * @var PaymentActionRepository
     */
    private $paymentActionRepository;

    /**
     * PaymentActionService constructor.
     */
    public function __construct(
        PaymentActionFactory $paymentActionFactory,
        PaymentActionRepository $paymentActionRepository
    ) {
        $this->paymentActionFactory = $paymentActionFactory;
        $this->paymentActionRepository = $paymentActionRepository;
    }

    /**
     * storePaymentAction
     *
     * @param PaymentActionData $paymentActionData
     *
     * @return PaymentAction
     */
    public function storePaymentAction(PaymentActionData $paymentActionData): PaymentAction
    {
        $paymentAction = $this->paymentActionFactory->createByData($paymentActionData);

        $this->paymentActionRepository->persist($paymentAction);

        return $paymentAction;
    }

    /**
     * update
     *
     * @param PaymentAction $paymentAction
     * @param PaymentActionData $data
     *
     * @return void
     */
    public function update(PaymentAction $paymentAction, PaymentActionData $data): void
    {
        $this->paymentActionFactory->mapData($paymentAction, $data);

        $this->paymentActionRepository->persist($paymentAction);
    }
}