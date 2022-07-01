<?php

namespace App\Service\Debt;

use App\Entity\Debt;
use App\Entity\TransactionPartInterface;
use App\Entity\User;
use App\Repository\DebtRepository;
use App\Service\Transaction\TransactionPartDataInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class DebtService
{
    /**
     * @var DebtFactory
     */
    private $debtFactory;

    /**
     * @var DebtRepository
     */
    private $debtRepository;

    /**
     * DebtService constructor.
     *
     * @param DebtFactory    $debtFactory
     * @param DebtRepository $debtRepository
     */
    public function __construct(
        DebtFactory $debtFactory,
        DebtRepository $debtRepository
    ) {
        $this->debtFactory = $debtFactory;
        $this->debtRepository = $debtRepository;
    }

    /**
     * storeDebt
     *
     * @param DebtCreateData $debtData
     *
     * @return Debt
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function storeDebt(DebtCreateData $debtData): Debt
    {
        $debt = $this->debtFactory->createByData($debtData);

        $this->debtRepository->persist($debt);

        return $debt;
    }

    /**
     * update
     *
     * @param TransactionPartInterface           $debt
     * @param TransactionPartDataInterface $data
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(TransactionPartInterface $debt, TransactionPartDataInterface $data): void
    {
        $this->debtFactory->mapData($debt, $data);

        $this->debtRepository->persist($debt);
    }

    /**
     * getAllDebtTransactionsForUser
     *
     * @param User $user
     *
     * @return array
     */
    public function getAllDebtTransactionsForUser(User $user): array
    {
        return $this->debtRepository->findTransactionsForUser($user);
    }

    /**
     * getAllDebtTransactionsForUserAndSate
     *
     * @param User   $user
     * @param string $state
     *
     * @return array
     */
    public function getAllDebtTransactionsForUserAndState(User $user, string $state): array
    {
        return $this->debtRepository->findAllDebtsForUserAndState($user, $state);
    }

    /**
     * getTotalDebtsForUser
     *
     * @param User $user
     *
     * @return float
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function getTotalDebtsForUser(User $user): float
    {
        return $this->debtRepository->getTotalDebtsForUser($user);
    }
}
