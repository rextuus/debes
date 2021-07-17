<?php

namespace App\Service\Mailer;

use App\Entity\Transaction;
use App\Entity\User;
use App\Service\Debt\DebtService;
use App\Service\Transaction\Statistics\TransactionStatisticService;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Message;

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
    public function sendTransferMail(Transaction $transaction, User $debtor, User $loaner): void
    {
        $this->sendTransferMailToDebtor(
            $debtor->getEmail(),
            $debtor->getFirstName(),
            $loaner->getFirstName(),
            $transaction->getAmount(),
            $transaction->getReason(),
            $this->transactionStatisticService->getProblemsBetweenUsers($transaction),
            $this->transactionStatisticService->getTransactionBetweenUsers($transaction),
            $this->debtService->getTotalDebtsForUser($loaner)
        );

        //todo: should user get a mail by itself???
    }

    /**
     * sendCreationMail
     *
     * @param Transaction $transaction
     *
     * @return void
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws TransportExceptionInterface
     */
    public function sendCreationMail(Transaction $transaction, User $debtor, User $loaner): void
    {
        $this->sendNewDebtMailToDebtor(
            $debtor->getEmail(),
            $debtor->getFirstName(),
            $loaner->getFirstName(),
            $transaction->getAmount(),
            $transaction->getReason(),
            $this->transactionStatisticService->getProblemsBetweenUsers($transaction),
            $this->transactionStatisticService->getTransactionBetweenUsers($transaction),
            $this->debtService->getTotalDebtsForUser($loaner),
            $transaction->getSlug()
        );
    }

    /**
     * sendNewDebtMailToDebtor
     *
     * @param string $debtorMail
     * @param string $userName
     * @param string $loaner
     * @param float  $amount
     * @param string $reason
     * @param int    $problems
     * @param int    $transactions
     * @param int    $debts
     * @param string $slug
     *
     * @return void
     * @throws TransportExceptionInterface
     */
    private function sendNewDebtMailToDebtor(
        string $debtorMail,
        string $userName,
        string $loaner,
        float $amount,
        string $reason,
        int $problems,
        int $transactions,
        int $debts,
        string $slug
    ): void {
        $text = 'Es gibt leider schlechte Nachrichten. Jemand hat eine neue Schuldlast für deinen Debes-Account hinterlegt';
        $subject = 'Du hast neue Schulden gemacht';

        $email = (new TemplatedEmail())
            ->from(self::DEBES_MAIL)
            ->to($debtorMail)
            ->subject($subject)
            ->htmlTemplate('mailer/mail.created.html.twig')
            ->context([
                          'userName' => $userName,
                          'text' => $text,
                          'loaner' => $loaner,
                          'reason' => $reason,
                          'amount' => $amount,
                          'problems' => $problems,
                          'transactions' => $transactions,
                          'debts' => $debts,
                          'slug' => $slug,
                      ]);

        $this->mailer->send($email);
    }


    /**
     * sendTransferMailToDebtor
     *
     * @param string $debtorMail
     * @param string $userName
     * @param string $loaner
     * @param float  $amount
     * @param string $reason
     * @param int    $problems
     * @param int    $transactions
     * @param int    $debts
     *
     * @return void
     * @throws TransportExceptionInterface
     */
    private function sendTransferMailToDebtor(
        string $debtorMail,
        string $userName,
        string $loaner,
        float $amount,
        string $reason,
        int $problems,
        int $transactions,
        int $debts
    ): void {
        $text = 'Es gibt gute Neuigkeiten. Jemand hat dir Geld überwiesen';
        $subject = 'Du hast eine Überweisung erhalten';

        $email = (new TemplatedEmail())
            ->from(self::DEBES_MAIL)
            ->to($debtorMail)
            ->subject($subject)
            ->htmlTemplate('mailer/mail.transferred.html.twig')
            ->context([
                          'userName' => $userName,
                          'text' => $text,
                          'loaner' => $loaner,
                          'reason' => $reason,
                          'amount' => $amount,
                          'problems' => $problems,
                          'transactions' => $transactions,
                          'debts' => $debts,
                      ]);

        $this->mailer->send($email);
    }
}