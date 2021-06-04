<?php

namespace App\Form;

use App\Service\PaymentOption\PaypalAccountUpdateData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PaypalAccountUpdateType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class)
            ->add('description', TextType::class)
            ->add('enabled', CheckboxType::class, ['required' => false])
            ->add('preferred', CheckboxType::class, ['required' => false])
            ->add('submit', SubmitType::class, ['label' => 'Änderungen sichern']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PaypalAccountUpdateData::class,
        ]);
    }
}
