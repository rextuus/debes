<?php

namespace App\Service\PaymentOption;

use App\Entity\PaypalAccount;
use App\Entity\User;
use App\Repository\PaypalAccountRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;

class PaypalAccountService
{

    /**
     * @var PaymentOptionFactory
     */
    private $paymentOptionFactory;

    /**
     * @var PaypalAccountRepository
     */
    private $paypalAccountRepository;

    /**
     * PaypalAccountService constructor.
     *
     * @param PaymentOptionFactory    $paymentOptionFactory
     * @param PaypalAccountRepository $paypalAccountRepository
     */
    public function __construct(
        PaymentOptionFactory $paymentOptionFactory,
        PaypalAccountRepository $paypalAccountRepository
    ) {
        $this->paymentOptionFactory = $paymentOptionFactory;
        $this->paypalAccountRepository = $paypalAccountRepository;
    }

    /**
     * storePaypalAccount
     *
     * @param PaypalAccountData $paypalAccountData
     *
     * @return void
     * @throws Exception
     */
    public function storePaypalAccount(PaypalAccountData $paypalAccountData): void
    {
        /** @var PaypalAccount $paypalAccount */
        $paypalAccount = $this->paymentOptionFactory->createByData($paypalAccountData);

        $this->paypalAccountRepository->persist($paypalAccount);
    }

    /**
     * getPaypalAccountsOfUser
     *
     * @param User $user
     *
     * @return array
     */
    public function getPaypalAccountsOfUser(User $user): array
    {
        $accounts = $this->paypalAccountRepository->findBy(['owner' => $user]);
        $dtos = array();
        foreach ($accounts as $account) {
            $dtos[] = $this->createDtoFromEntity($account);
        }
        return $dtos;
    }

    /**
     * getPaypalAccountCountForUser
     *
     * @param User $requester
     *
     * @return string
     */
    public function getCurrentPaypalAccountDescriptionHint(User $requester): string
    {
        return 'Paypal_Konto_' . ($this->paypalAccountRepository->getPaypalAccountCountForUser($requester) + 1);
    }

    /**
     * update
     *
     * @param PaypalAccount           $paypalAccount
     * @param PaypalAccountUpdateData $data
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(PaypalAccount $paypalAccount, PaypalAccountUpdateData $data): void
    {
        $this->paymentOptionFactory->mapData($paypalAccount, $data);

        $this->paypalAccountRepository->persist($paypalAccount);
    }

    /**
     * createDtoFromEntity
     *
     * @param PaypalAccount $account
     *
     * @return PaymentOptionDTO
     */
    private function createDtoFromEntity(PaypalAccount $account): PaymentOptionDTO
    {
        $dto = new PaymentOptionDTO();
        $dto->setIsBankAccount(false);
        $dto->setIsPaypalAccount(true);
        $dto->setEnabled($account->getEnabled());
        $dto->setAccountId($account->getId());
        $dto->setIsPrioritised($account->getIsPrioritised());
        return $dto;
    }

    /**
     * getPaypalAccountForUser
     *
     * @param User $user
     *
     * @return PaypalAccount|null
     */
    public function getPaypalAccountForUser(User $user)
    {
        $default = $this->paypalAccountRepository->findOneBy(
            [
                'owner' => $user,
                'enabled' => true,
                'isPrioritised' => true,
            ]
        );

        if (!$default) {
            $default = $this->paypalAccountRepository->findOneBy(
                [
                    'owner' => $user,
                    'enabled' => true,
                ]
            );
        }

        return $default;
    }
}
