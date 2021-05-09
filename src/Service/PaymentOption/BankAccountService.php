<?php

namespace App\Service\PaymentOption;

use App\Entity\BankAccount;
use App\Entity\User;
use App\Repository\BankAccountRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;

class BankAccountService
{

    /**
     * @var PaymentOptionFactory
     */
    private $paymentOptionFactory;

    /**
     * @var BankAccountRepository
     */
    private $bankAccountRepository;

    /**
     * BankAccountService constructor.
     *
     * @param PaymentOptionFactory  $paymentOptionFactory
     * @param BankAccountRepository $bankAccountRepository
     */
    public function __construct(
        PaymentOptionFactory $paymentOptionFactory,
        BankAccountRepository $bankAccountRepository
    ) {
        $this->paymentOptionFactory = $paymentOptionFactory;
        $this->bankAccountRepository = $bankAccountRepository;
    }

    /**
     * storeBankAccount
     *
     * @param BankAccountData $bankAccountData
     *
     * @return void
     * @throws Exception
     */
    public function storeBankAccount(BankAccountData $bankAccountData): void
    {
        /** @var BankAccount $bankAccount */
        $bankAccount = $this->paymentOptionFactory->createByData($bankAccountData);

        $this->bankAccountRepository->persist($bankAccount);
    }

    /**
     * update
     *
     * @param BankAccount           $bankAccount
     * @param BankAccountUpdateData $data
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(BankAccount $bankAccount, BankAccountUpdateData $data): void
    {
        $this->paymentOptionFactory->mapData($bankAccount, $data);

        $this->bankAccountRepository->persist($bankAccount);
    }

    /**
     * getBackAccountsOfUser
     *
     * @param User $user
     *
     * @return array
     */
    public function getBankAccountsOfUser(User $user): array
    {
        $accounts = $this->bankAccountRepository->findBy(['owner' => $user]);
        $dtos = array();
        foreach ($accounts as $account){
            $dtos[] = $this->createDtoFromEntity($account);
        }
        return $dtos;
    }

    /**
     * createDtoFromEntity
     *
     * @param BankAccount $account
     *
     * @return PaymentOptionDTO
     */
    private function createDtoFromEntity(BankAccount $account): PaymentOptionDTO
    {
        $dto = new PaymentOptionDTO();
        $dto->setIsBankAccount(true);
        $dto->setIsPaypalAccount(false);
        $dto->setEnabled($account->getEnabled());
        $dto->setAccountId($account->getId());
        return $dto;
    }
}