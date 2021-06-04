<?php

namespace App\Form;

use App\Service\PaymentOption\BankAccountUpdateData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankAccountUpdateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('iban', TextType::class)// TODO IBAN + BIC CHECKER
            ->add('bic', TextType::class)
            ->add('accountName', TextType::class)
            ->add('bankName', TextType::class)
            ->add('description', TextType::class)
            ->add('enabled', CheckboxType::class, ['required' => false])
            ->add('preferred', CheckboxType::class, ['required' => false])
            ->add('submit', SubmitType::class, ['label' => 'Änderungen speichern']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BankAccountUpdateData::class,
        ]);
    }
}
