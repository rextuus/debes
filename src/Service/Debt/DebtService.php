<?php

namespace App\Service\Debt;

use App\Entity\Debt;
use App\Entity\User;
use App\Repository\DebtRepository;
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
     * @return void
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function storeDebt(DebtCreateData $debtData): void
    {
        $debt = $this->debtFactory->createByData($debtData);

        $this->debtRepository->persist($debt);
    }

    /**
     * update
     *
     * @param Debt           $debt
     * @param DebtUpdateData $data
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function update(Debt $debt, DebtUpdateData $data): void
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
        return $this->debtRepository->findTransactionsForUserAndState($user, $state);
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
