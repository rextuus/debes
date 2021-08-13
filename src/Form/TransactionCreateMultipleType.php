<?php

namespace App\Form;

use App\Entity\User;
use App\Service\Transaction\TransactionCreateMultipleData;
use App\Service\User\UserService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionCreateMultipleType extends AbstractType
{
    /**
     * @var UserService
     */
    private $userService;

    /**
     * TransactionCreateSimpleType constructor.
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $numberOfCandidates = count($this->userService->findAllOther($options['requester']));
        $choices = array();
        foreach (range(1, $numberOfCandidates) as $index){
            $choices[$index] = (string) $index;
        }
        $builder->add('completeAmount', NumberType::class, ['label' => 'Gesamtschule', 'attr' => ['completeAmount' => true]]);
        $builder->add('debtors', ChoiceType::class, ['label' => 'Transaktion erstellen', 'choices' => $choices, ]);
        $builder->add('reason', TextType::class, ['label' => 'Grund']);
        $builder->add('submit', SubmitType::class, ['label' => 'Transaktion erstellen']);

        $builder->add('debtorsData', CollectionType::class, [
            'allow_add'     => true,
            'allow_delete'  => true,
            'prototype'  => true,
            'entry_type'    => DebtCreateType::class,
            'entry_options' => [
                'requester' => $options['requester'],
                'attr'          => [
                    'class' => 'js-genus-scientist-item',
                ],
            ],
            'by_reference' => false,
        ]);
    }

    /**
     * configureOptions
     *
     * @param OptionsResolver $resolver
     *
     * @return void
     *
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TransactionCreateMultipleData::class,
            'requester' => User::class,
        ]);
    }

}