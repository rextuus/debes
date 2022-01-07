<?php

namespace App\Form;

use App\Entity\Debt;
use App\Entity\Loan;
use App\Entity\Transaction;
use App\Service\Loan\LoanDto;
use App\Service\Loan\LoanService;
use App\Service\Transfer\ExchangeProcessor;
use App\Service\Transfer\PrepareExchangeTransferData;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ExchangeType
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class ExchangeType extends AbstractType
{
    /**
     * @var LoanService
     */
    private $loanService;

    /**
     * TransactionCreateSimpleType constructor.
     */
    public function __construct(LoanService $loanService)
    {
        $this->loanService = $loanService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'loan',
                ChoiceType::class,
                [
                    'choices' => $this->prepareOptions($options['debt']),
                    'data' => $options['debt'],
                ]
            )
            ->add('submit', SubmitType::class, ['label' => 'Mit dieser Transaktion verrechnen'])
            ->add('decline', SubmitType::class, ['label' => 'Abbrechen']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PrepareExchangeTransferData::class,
            'debt' => Debt::class,
        ]);
    }

    /**
     * prepareOptions
     *
     * @param Debt $debt
     *
     * @return array
     */
    private function prepareOptions(Debt $debt): array
    {
        $candidates = $this->loanService->getAllExchangeLoansForDebt($debt);
        $choices = array();
        foreach ($candidates as $candidate) {
            /** @var Loan $candidate */
            $choices[(string) $candidate] = $candidate;
        }
        return $choices;
    }
}