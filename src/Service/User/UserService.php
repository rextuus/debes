<?php

namespace App\Service\User;

use App\Repository\UserRepository;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;

class UserService
{

    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var UserFactory
     */
    private $userFactory;

    /**
     * UserService constructor.
     *
     * @param UserRepository $userRepository
     * @param UserFactory    $userFactory
     */
    public function __construct(UserRepository $userRepository, UserFactory $userFactory)
    {
        $this->userRepository = $userRepository;
        $this->userFactory = $userFactory;
    }

    /**
     * storeUser
     *
     * @param UserData $userData
     *
     * @return void
     *
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function storeUser(UserData $userData): void
    {
        $user = $this->userFactory->createByData($userData);

        $this->userRepository->persist($user);
    }
}
