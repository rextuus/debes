<?php

namespace App\Service\PaymentOption;

use App\Entity\User;
use App\Repository\PaymentOptionRepository;

class PaymentOptionService
{
    /**
     * @var PaymentOptionRepository
     */
    private $paymentOptionRepository;

    /**
     * PaymentOptionService constructor.
     *
     * @param PaymentOptionRepository $paymentOptionRepository
     */
    public function __construct(
        PaymentOptionRepository $paymentOptionRepository
    ) {
        $this->paymentOptionRepository = $paymentOptionRepository;
    }

    /**
     * getPaymentOptionsOfUser
     *
     * @param User $user
     *
     * @return array
     */
    public function getPaymentOptionsOfUser(User $user): array
    {
        return $this->paymentOptionRepository->findBy(['owner' => $user]);
    }
}