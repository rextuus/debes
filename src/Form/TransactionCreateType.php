<?php

namespace App\Form;

use App\Entity\Transaction;
use App\Service\Debt\DebtCreateData;
use App\Service\Transaction\TransactionCreateData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TransactionCreateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $optionArray = array();
        foreach (range(1, 20) as $debtorNr){
            $optionArray[(string) $debtorNr] = $debtorNr;
        }

        $builder
            ->add('amount', MoneyType::class)
            ->add('reason', TextType::class)
            ->add(
                'debtors',
                ChoiceType::class,
                [
                    'choices' => $optionArray,
                    'data' => '1'
                ]
            )
            ->add(
                'loaners',
                ChoiceType::class,
                [
                    'choices' => $optionArray
                ]
            );


        $builder->add('submit', SubmitType::class, ['label' => 'Zu den Details']);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TransactionCreateData::class,
        ]);
    }
}