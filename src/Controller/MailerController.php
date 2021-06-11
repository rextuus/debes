<?php

namespace App\Controller;

use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class MailerController extends AbstractController
{
    /**
     * @Route("/mailer", name="mailer")
     */
    public function index(MailerInterface $mailer)
    {
        $userName = 'Dieter';
        $text = 'Es gibt leider schlechte Nachrichten. Jemand hat eine neue Schuldlast für deinen Debes-Account hinterlegt';
        $loaner = 'Hans';
        $amount = 19.48;
        $reason = 'Essen bei Kuma. Es musste aber in mehrere Teile aufegeutl wertden, weil es gar nciht geschmecjt gat';
        $subject = 'Du hast neue Schulden gemacht';
        $problems = 0;
        $transactions = 0;
        $debts = 0;
        $email = (new TemplatedEmail())
            ->from('debes@wh-company.de')
            ->to('wrextuus@gmail.com')
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
            ]);

        $mailer->send($email);


        return $this->render('mailer/index.html.twig', [
            'userName' => $userName,
            'text' => $text,
            'loaner' => $loaner,
            'reason' => $reason,
            'amount' => $amount,
            'problems' => $problems,
            'transactions' => $transactions,
            'debts' => $debts,

        ]);
    }
}
