<?php

namespace App\Form;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Debt\DebtCreateData;
use App\Service\Transaction\TransactionCreateData;
use App\Service\Transaction\TransactionCreateDebtorData;
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

class TransactionCreateDebtorType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        dump($options['debtors']);
        foreach (range(1, $options['debtors']) as $debtorNr) {
            $name = sprintf('debtor%d', $debtorNr);
            $builder->add($name, DebtCreateType::class, ['requester' => $options['requester']]);
        }

        $builder->add('submit', SubmitType::class, ['label' => 'Transaktion erstellen']);
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => TransactionCreateDebtorData::class,
            'debtors' => 1,
            'requester' => User::class
        ]);
        $resolver->setAllowedTypes('debtors', 'int');
    }
}