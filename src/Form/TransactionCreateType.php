<?php

namespace App\Form;

use App\Entity\Transaction;
use App\Service\Debt\DebtCreateData;
use App\Service\Transaction\TransactionCreateData;
use App\Service\User\UserService;
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
    /**
     * @var UserService
     */
    private $userService;

    /**
     * TransactionCreateType constructor.
     */
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * addUsers
     *
     * @param FormBuilderInterface $builder
     *
     * @return void
     */
    protected function addUsers(FormBuilderInterface $builder): void
    {
        $users = $this->userService->findAllOther();

        $options = self::CHILD_OCCUPATIONS_OPTIONS['professions'];

        $min = array_key_exists('min', $options) && !empty($options['min']) ? $options['min'] : null;
        $max = array_key_exists('max', $options) && !empty($options['max']) ? $options['max'] : null;

        $this->addOccupation($builder, 'professions', $users, $min, $max);

        $builder->get('professions')
            ->addEventListener(
                FormEvents::PRE_SET_DATA,
                function (FormEvent $event) {
                    $this->onPreSetDataProfessions($event);
                }
            );

        $builder->get('professions')
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) {
                    $this->onPostSubmitProfessions($event);
                }
            );
    }


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