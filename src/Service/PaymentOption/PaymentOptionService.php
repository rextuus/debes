<?php

namespace App\Service\PaymentOption;

use App\Entity\PaymentOption;
use App\Entity\User;
use App\Repository\BankAccountRepository;
use App\Repository\PaypalAccountRepository;

class PaymentOptionService
{
    /**
     * @var PaypalAccountRepository
     */
    private $paypalAccountRepository;

    /**
     * @var BankAccountRepository
     */
    private $bankAccountRepository;

    /**
     * PaymentOptionService constructor.
     *
     * @param PaypalAccountRepository $paypalAccountRepository
     * @param BankAccountRepository   $bankAccountRepository
     */
    public function __construct(
        PaypalAccountRepository $paypalAccountRepository,
        BankAccountRepository $bankAccountRepository
    ) {
        $this->paypalAccountRepository = $paypalAccountRepository;
        $this->bankAccountRepository = $bankAccountRepository;
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
    }

    /**
     * getActivePaymentOptionsOfUser
     *
     * @param User $user
     * @param bool $includeBank
     * @param bool $includePaypal
     *
     * @return array
     */
    public function getActivePaymentOptionsOfUser(User $user, bool $includeBank = true, bool $includePaypal = true): array
    {
        $preferredBank= [];
        $otherBank= [];
        $preferredPaypal= [];
        $otherPaypal= [];

        if ($includeBank){
            $preferredBank = $this->bankAccountRepository->findBy(
                [
                    'owner' => $user,
                    'enabled' => true,
                    'isPrioritised' => true,
                ]
            );

            $otherBank = $this->bankAccountRepository->findBy(
                [
                    'owner' => $user,
                    'enabled' => true,
                    'isPrioritised' => false,
                ]
            );
        }

        if ($includePaypal){
            $preferredPaypal = $this->paypalAccountRepository->findBy(
                [
                    'owner' => $user,
                    'enabled' => true,
                    'isPrioritised' => true,
                ]
            );
            $otherPaypal = $this->paypalAccountRepository->findBy(
                [
                    'owner' => $user,
                    'enabled' => true,
                    'isPrioritised' => false,
                ]
            );
        }
        return array_merge($preferredBank, $preferredPaypal, $otherBank, $otherPaypal);
    }

    /**
     * getDefaultPaymentOptionForUser
     *
     * @param User $user
     *
     * @return PaymentOption
     */
    public function getDefaultPaymentOptionForUser(User $user): PaymentOption
    {
        $default = $this->bankAccountRepository->findOneBy(
            [
                'owner' => $user,
                'enabled' => true,
                'isPrioritised' => true,
            ]
        );

        if (!$default){
            $default = $this->paypalAccountRepository->findOneBy(
                [
                    'owner' => $user,
                    'enabled' => true,
                    'isPrioritised' => true,
                ]
            );
        }

        if (!$default){
            $default = $this->bankAccountRepository->findOneBy(
                [
                    'owner' => $user,
                    'enabled' => true
                ]
            );
        }

        if (!$default){
            $default = $this->paypalAccountRepository->findOneBy(
                [
                    'owner' => $user,
                    'enabled' => true
                ]
            );
        }
        return $default;
    }
}