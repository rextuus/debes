<?php

namespace App\Service\Mailer;

use App\Entity\PaymentAction;
use App\Entity\Transaction;
use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;

/**
 * MailService
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class MailService
{

    private const DEBES_MAIL_ADDRESS = 'debes@wh-company.de';
    public const MAIL_DEBT_CREATED = 'debt_created';
    public const MAIL_DEBT_CANCELED = 'debt_canceled';
    public const MAIL_DEBT_ACCEPTED = 'debt_accepted';
    public const MAIL_DEBT_DECLINED = 'debt_declined';
    public const MAIL_DEBT_PAYED_PAYPAL = 'debt_payed_paypal';
    public const MAIL_DEBT_PAYED_ACCOUNT = 'debt_payed_account';
    public const MAIL_DEBT_TRANSFERRED = 'debt_transferred';
    public const MAIL_DEBT_CONFIRMED = 'debt_confirmed';

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * MailService constructor.
     * @param MailerInterface $mailer
     */
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    /**
     * @param Transaction $transaction
     * @param string $mailVariant
     * @param PaymentAction|null $paymentAction
     * @throws \Symfony\Component\Mailer\Exception\TransportExceptionInterface
     */
    public function sendNotificationMail(Transaction $transaction, string $mailVariant, PaymentAction $paymentAction = null){
        $receiver = $transaction->getDebts()[0]->getOwner();

        $subject = '';
        $text = '';
        $template = '';
        $slug = $transaction->getSlug();

        switch ($mailVariant) {
            case self::MAIL_DEBT_CREATED:
                $text = 'Es gibt leider schlechte Nachrichten. Jemand hat eine neue Schuldlast für deinen Debes-Account hinterlegt';
                $subject = 'Neue Schulden';
                $template = 'mailer/mail.created.html.twig';
                $receiver = $transaction->getDebts()[0]->getOwner();
                break;
            case self::MAIL_DEBT_CANCELED:
                $text = 'Es gibt gute Nachrichten. Jemand hat eine Schuldlast für deinen Debes-Account zurückgezogen';
                $subject = 'Schuld zurückgezogen';
                $template = 'mailer/mail.canceled.html.twig';
                $receiver = $transaction->getDebts()[0]->getOwner();
                break;
            case self::MAIL_DEBT_ACCEPTED:
                $text = 'Es gibt gute Nachrichten. Jemand hat eine Schuldenforderung von dir akzeptiert';
                $subject = 'Schuldlast akzeptiert ';
                $template = 'mailer/mail.accepted.html.twig';
                $receiver = $transaction->getLoans()[0]->getOwner();
                break;            case
            self::MAIL_DEBT_DECLINED:
                $text = 'Es gibt schlechte Nachrichten. Jemand hat eine Schuldenforderung von dir abgewiesen';
                $subject = 'Schuldlast abgelehnt ';
                $template = 'mailer/mail.declined.html.twig';
                $receiver = $transaction->getLoans()[0]->getOwner();
                break;
            case self::MAIL_DEBT_TRANSFERRED:
                $text = 'Es gibt gute Nachrichten. Jemand hat eine Schuld beglichen und dir Geld überwiesen';
                $subject = 'Schulden zurück erhalten';
                $template = 'mailer/mail.transferred.html.twig';
                $receiver = $transaction->getLoans()[0]->getOwner();
                break;
            case self::MAIL_DEBT_PAYED_ACCOUNT:
                $text = 'Es gibt gute Nachrichten. Jemand hat eine Schuld beglichen und dir Geld auf dein Bank-Konto überwiesen';
                $subject = 'Schulden zurück erhalten';
                $template = 'mailer/mail.transferred.html.twig';
                $receiver = $transaction->getLoans()[0]->getOwner();
                break;
            case self::MAIL_DEBT_PAYED_PAYPAL:
                $text = 'Es gibt gute Nachrichten. Jemand hat eine Schuld beglichen und dir Geld auf dein Paypal-Konto überwiesen';
                $subject = 'Schulden zurück erhalten';
                $template = 'mailer/mail.transferred.html.twig';
                $receiver = $transaction->getLoans()[0]->getOwner();
                break;
            case self::MAIL_DEBT_CONFIRMED:
                $text = 'Es gibt gute Nachrichten. Jemand hat den Eingang deiner Schuldrückzahlung bestätigt';
                $subject = 'Geldeingang bestätigt';
                $template = 'mailer/mail.confirmed.html.twig';
                $receiver = $transaction->getLoans()[0]->getOwner();
                break;
        }

        $problems = 0;
        $transactions = 0;
        $debts = 0;

        $email = (new TemplatedEmail())
            ->from(self::DEBES_MAIL_ADDRESS)
            ->to($receiver->getEmail())
            ->subject($subject)
            ->htmlTemplate($template)
            ->context([
                'userName' => $receiver->getFirstName(),
                'text' => $text,
                'interacter' => $transaction->getLoans()[0]->getOwner()->getFirstName(),
                'reason' => $transaction->getReason(),
                'amount' => $transaction->getAmount(),
                'problems' => $problems,
                'transactions' => $transactions,
                'debts' => $debts,
                'slug' => $slug,
                'paymentAction' => $paymentAction,
            ]);

        $this->mailer->send($email);
    }
}