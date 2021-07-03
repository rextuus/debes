<?php

namespace App\Service\Legacy;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\PaymentOption\BankAccountData;
use App\Service\PaymentOption\BankAccountService;
use App\Service\Transaction\TransactionCreateData;
use App\Service\Transaction\TransactionService;
use App\Service\User\UserData;
use App\Service\User\UserService;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Exception;

/**
 * LegacyImportService
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class LegacyImportService
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var BankAccountService
     */
    private $bankAccountService;

    private $transactionService;

    /**
     * LoadFixtureFilesToDatabase constructor.
     */
    public function __construct(
        UserService $userService,
        BankAccountService $bankAccountService,
        TransactionService $transactionService
    ) {
        $this->userService = $userService;
        $this->bankAccountService = $bankAccountService;
        $this->transactionService = $transactionService;
    }

    /**
     * createUserByData
     *
     * @param string $email
     * @param string $password
     * @param string $firstName
     * @param string $lastName
     * @param string $userName
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createUserByData(
        string $email,
        string $password,
        string $firstName,
        string $lastName,
        string $userName
    ): void {
        $userData = new UserData();
        $userData->setEmail($email);
        $userData->setPassword($password);
        $userData->setLastName($lastName);
        $userData->setFirstName($firstName);
        $userData->setUserName($userName);
        $this->userService->storeUser($userData);
    }

    /**
     * creatBankAccountByData
     *
     * @param bool   $enabled
     * @param string $bankName
     * @param string $bic
     * @param string $iban
     * @param string $description
     * @param string $preferred
     * @param User   $owner
     * @param string $accountName
     *
     * @return void
     * @throws Exception
     */
    public function creatBankAccountByData(
        bool $enabled,
        string $bankName,
        string $bic,
        string $iban,
        string $description,
        string $preferred,
        User $owner,
        string $accountName
    ): void {
        $bankAccountData = new BankAccountData();
        $bankAccountData->setEnabled($enabled);
        $bankAccountData->setBankName($bankName);
        $bankAccountData->setBic($bic);
        $bankAccountData->setIban($iban);
        $bankAccountData->setDescription($description);
        $bankAccountData->setPreferred($preferred);
        $bankAccountData->setOwner($owner);
        $bankAccountData->setAccountName($accountName);
        $this->bankAccountService->storeBankAccount($bankAccountData);
    }

    /**
     * createTransaction
     *
     * @param string      $reason
     * @param float       $amount
     * @param User        $debtor
     * @param User        $loaner
     * @param string|null $state
     *
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function createTransaction(
        string $reason,
        float $amount,
        User $debtor,
        User $loaner,
        ?string $state = Transaction::STATE_READY
    ): void {
        $transactionData = new TransactionCreateData();
        $transactionData->setReason($reason);
        $transactionData->setAmount($amount);
        $transactionData->setOwner($debtor);

        $transaction = $this->transactionService->storeSimpleTransaction($transactionData, $loaner);
    }
}