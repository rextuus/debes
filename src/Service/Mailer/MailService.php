<?php

namespace App\Service\Mailer;

use App\Entity\PaymentAction;
use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Debt\DebtService;
use App\Service\Transaction\Statistics\TransactionStatisticService;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;

/**
 * MailService
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class MailService
{
    private const DEBES_MAIL = 'debes@wh-company.de';

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * @var TransactionStatisticService
     */
    private $transactionStatisticService;

    /**
     * @var DebtService
     */
    private $debtService;

    /**
     * MailService constructor.
     */
    public function __construct(
        MailerInterface $mailer,
        TransactionStatisticService $transactionStatisticService,
        DebtService $debtService
    ) {
        $this->mailer = $mailer;
        $this->transactionStatisticService = $transactionStatisticService;
        $this->debtService = $debtService;
    }

    /**
     * sendCreationMail
     *
     * @param Transaction $transaction
     * @param User        $debtor
     * @param User        $loaner
     *
     * @return void
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws TransportExceptionInterface
     */
    public function sendCreationMailToDebtor(Transaction $transaction, User $debtor, User $loaner): void
    {
        $text = 'Es gibt leider schlechte Nachrichten. Jemand hat eine neue Schuldlast für deinen Debes-Account hinterlegt';
        $subject = 'Du hast neue Schulden gemacht';

        $context = [
            'userName' => $debtor->getFirstName(),
            'text' => $text,
            'loaner' => $loaner->getFirstName(),
            'slug' => $transaction->getSlug(),
        ];
        $this->addStandardInfosToContext($context, $transaction, $debtor);

        $this->sendMail(
            $debtor->getEmail(),
            $subject,
            'mailer/mail.created.html.twig',
            $context
        );
    }

    /**
     * sendAcceptMail
     *
     * @param Transaction $transaction
     * @param User        $debtor
     * @param User        $loaner
     *
     * @return void
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws TransportExceptionInterface
     */
    public function sendAcceptMailToLoaner(Transaction $transaction, User $debtor, User $loaner): void
    {
        $text = 'Es gibt gute Neuigkeiten. Jemand hat hat eine Schuld anerkannt';
        $subject = 'Dein Darlehen wurde akzeptiert';

        $context = [
            'userName' => $loaner->getFirstName(),
            'text' => $text,
            'debtor' => $debtor->getFirstName(),
        ];
        $this->addStandardInfosToContext($context, $transaction, $loaner);

        $this->sendMail(
            $loaner->getEmail(),
            $subject,
            'mailer/mail.accepted.html.twig',
            $context
        );
    }

    /**
     * sendDeclineMail
     *
     * @param Transaction $transaction
     * @param User        $debtor
     * @param User        $loaner
     *
     * @return void
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws TransportExceptionInterface
     */
    public function sendDeclineMailToLoaner(Transaction $transaction, User $debtor, User $loaner): void
    {
        $text = 'Es gibt schlechte Neuigkeiten. Jemand weigert sich eine Schuld anzuerkennen';
        $subject = 'Dein Darlehen wurde abgelehnt';

        $context = [
            'userName' => $loaner->getFirstName(),
            'text' => $text,
            'debtor' => $debtor->getFirstName(),
        ];
        $this->addStandardInfosToContext($context, $transaction, $loaner);

        $this->sendMail(
            $loaner->getEmail(),
            $subject,
            'mailer/mail.declined.html.twig',
            $context
        );
    }

    /**
     * sendTransferMail
     *
     * @param Transaction $transaction
     * @param User        $debtor
     * @param User        $loaner
     *
     * @return void
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws TransportExceptionInterface
     */
    public function sendTransferMailToLoaner(
        Transaction $transaction,
        User $debtor,
        User $loaner,
        PaymentAction $paymentAction
    ): void {
        $text = 'Es gibt gute Neuigkeiten. Jemand hat dir Geld überwiesen';
        $subject = 'Du hast eine Überweisung erhalten';

        $context = [
            'userName' => $loaner->getFirstName(),
            'text' => $text,
            'loaner' => $loaner->getFirstName(),
            'paymentAction' => $paymentAction,
        ];
        $this->addStandardInfosToContext($context, $transaction, $loaner);

        $this->sendMail(
            $loaner->getEmail(),
            $subject,
            'mailer/mail.transferred.html.twig',
            $context
        );
    }

    /**
     * addStandardInfosToContext
     *
     * @param array $context
     * @param Transaction $transaction
     * @param User $user
     *
     * @return void
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    private function addStandardInfosToContext(array &$context, Transaction $transaction, User $user): void
    {
        $context['reason'] = $transaction->getReason();
        $context['amount'] = $transaction->getAmount();
        $context['problems'] = $this->transactionStatisticService->getProblemsBetweenUsers($transaction);
        $context['transactions'] = $this->transactionStatisticService->getTransactionBetweenUsers($transaction);
        $context['debts'] = $this->debtService->getTotalDebtsForUser($user);
    }

    /**
     * sendMail
     *
     * @param string $receiverMail
     * @param string $subject
     * @param string $template
     * @param array  $context
     *
     * @return void
     * @throws TransportExceptionInterface
     */
    private function sendMail(
        string $receiverMail,
        string $subject,
        string $template,
        array $context
    ) {
        $email = (new TemplatedEmail())
            ->from(self::DEBES_MAIL)
            ->to($receiverMail)
            ->subject($subject)
            ->htmlTemplate($template)
            ->context($context);
        $this->mailer->send($email);
    }
}