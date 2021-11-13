<?php

namespace App\Service\Mailer;

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
    private const MAIL_DEBT_CREATED = 'debt_created';
    private const MAIL_DEBT_ACCEPTED = 'debt_accepted';
    private const MAIL_DEBT_ = 'debt_accepted';

    /**
     * @var MailerInterface
     */
    private $mailer;

    /**
     * MailService constructor.
     */
    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendNotificationMail(User $user, Transaction $transaction, string $mailVariant){
        $receiver = $transaction->getDebts()[0]->getOwner();

        $subject = '';
        $text = '';
        $template = '';
        if ($mailVariant === self::MAIL_NEW_DEBT){
            $text = 'Es gibt leider schlechte Nachrichten. Jemand hat eine neue Schuldlast für deinen Debes-Account hinterlegt';
            $subject = 'Du hast neue Schulden gemacht';
            $template = 'mailer/mail.created.html.twig';
        }elseif ($mailVariant === self::MAIL_NEW_DEBT){
            $text = 'Es gibt leider schlechte Nachrichten. Jemand hat eine neue Schuldlast für deinen Debes-Account hinterlegt';
            $subject = 'Du hast neue Schulden gemacht';
            $template = 'mailer/mail.created.html.twig';
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
                'loaner' => $transaction->getLoans()[0]->getOwner()->getFirstName(),
                'reason' => $transaction->getReason(),
                'amount' => $transaction->getAmount(),
                'problems' => $problems,
                'transactions' => $transactions,
                'debts' => $debts,
            ]);

        $this->mailer->send($email);
    }
}