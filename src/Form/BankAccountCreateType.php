<?php

namespace App\Form;

use App\Service\PaymentOption\BankAccountData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BankAccountCreateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('iban', TextType::class)// TODO IBAN + BIC CHECKER
            ->add('bic', TextType::class)
            ->add('accountName', TextType::class)
            ->add('bankName', TextType::class)
            ->add('enabled', CheckboxType::class, ['required' => false])
            ->add('submit', SubmitType::class, ['label' => 'Bankkonto registrieren']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BankAccountData::class,
        ]);
    }
}
