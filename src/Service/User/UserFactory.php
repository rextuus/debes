<?php

namespace App\Service\User;

use App\Entity\User;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserFactory
{

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    /**
     * UserFactory constructor.
     */
    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }


    /**
     * createByData
     *
     * @param UserData $userData
     *
     * @return User
     */
    public function createByData(UserData $userData): User
    {
        $user = $this->createNewUserInstance();
        $this->mapData($user, $userData);

        return $user;
    }

    /**
     * mapData
     *
     * @param User     $user
     * @param UserData $userData
     */
    public function mapData(User $user, UserData $userData): void
    {
        $user->setUsername($userData->getUserName());
        $user->setEmail($userData->getEmail());
        $user->setFirstName($userData->getFirstName());
        $user->setLastName($userData->getLastName());
        $user->setPassword($this->passwordEncoder->encodePassword($user, $userData->getPassword()));
    }

    /**
     * createNewUserInstance
     *
     * @return User
     */
    private function createNewUserInstance(): User
    {
        return new User();
    }
}
