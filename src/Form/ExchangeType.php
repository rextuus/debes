<?php

namespace App\Form;

use App\Entity\Transaction;
use App\Service\Loan\LoanDto;
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
     * @var ExchangeProcessor
     */
    private $exchangeService;

    /**
     * TransactionCreateSimpleType constructor.
     */
    public function __construct(ExchangeProcessor $exchangeService)
    {
        $this->exchangeService = $exchangeService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'transactionSlug',
                ChoiceType::class,
                [
                    'choices' => $this->prepareOptions($options['transaction']),
                    'data' => $options['transaction'],
                ]
            )
            ->add('submit', SubmitType::class, ['label' => 'Mit dieser Transaktion verrechnen']);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PrepareExchangeTransferData::class,
            'transaction' => Transaction::class,
        ]);
    }

    /**
     * prepareOptions
     *
     * @param Transaction $transaction
     *
     * @return array
     */
    private function prepareOptions(Transaction $transaction): array
    {
        $candidates = $this->exchangeService->findExchangeCandidatesForTransaction($transaction)->getFittingCandidates();
        $candidates = array_merge($candidates, $this->exchangeService->findExchangeCandidatesForTransaction($transaction)->getNonFittingCandidates());
        $choices = array();
        foreach ($candidates as $candidate) {
            /** @var LoanDto $candidate */
            $choices[$candidate->getReason()] = $candidate->getSlug();
        }
        return $choices;
    }

    /**
     * prepareNonFittingOptions
     *
     * @param Transaction $transaction
     *
     * @return array
     */
    private function prepareNonFittingOptions(Transaction $transaction): array
    {
        $candidates = $this->exchangeService->findExchangeCandidatesForTransaction($transaction)->getNonFittingCandidates();
        $candidates = array_merge($candidates, $this->exchangeService->findExchangeCandidatesForTransaction($transaction)->getNonFittingCandidates());
        $choices = array();
        foreach ($candidates as $candidate) {
            /** @var LoanDto $candidate */
            $choices[$candidate->getReason()] = $candidate->getSlug();
        }
        return $choices;
    }
}