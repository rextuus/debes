<?php

namespace App\Service\Mailer;

use Symfony\Component\Mailer\MailerInterface;

/**
 * MailService
 *
 * @author  Wolfgang Hinzmann <wolfgang.hinzmann@doccheck.com>
 * @license 2021 DocCheck Community GmbH
 */
class MailService
{
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

    public function sendNotificationMail(){

    }
}