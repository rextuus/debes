<?php

namespace App\Form;

use App\Entity\User;
use App\Service\Debt\DebtCreateData;
use App\Service\User\UserService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DebtCreateType extends AbstractType
{

    /**
     * @var UserService
     */
    private $userService;

    /**
     * DebtCreateType constructor.
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', MoneyType::class)
            ->add(
                'owner',
                ChoiceType::class,
                [
                    'choices' => $this->prepareOptions($options['requester']),
                    'data' => $options['requester'],
                ]
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => DebtCreateData::class,
            'requester' => User::class,
        ]);
    }

    /**
     * prepareOptions
     *
     * @param User $requester
     *
     * @return array
     */
    private function prepareOptions(User $requester): array
    {
        $candidates = $this->userService->findAllOther($requester);
        $choices = array();
        foreach ($candidates as $candidate) {
            $choices[$candidate->getUsername()] = $candidate;
        }
        return $choices;
    }
}