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
    public function index(MailerInterface $mailer): Response
    {
        $userName = 'Dieter';
        $type = 1;
        $text = 'Test text für die mail';
        $email = (new TemplatedEmail())
            ->sender('wh.company.services@gmail.com')
            ->to('wrextuus@gmail.com')
            ->subject('Test')
            ->htmlTemplate('mailer/mail.html.twig')
            ->context([
                'userName' => $userName,
                'type' => $type
            ]);


//        $mailer->send($email);
        return $this->render('mailer/mail.html.twig', [
            'userName' => $userName,
            'type' => $type,
            'text' => $text
        ]);
    }
}
