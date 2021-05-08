<?php

namespace App\Controller;

use App\Entity\BankAccount;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    /**
     * @Route("/payment", name="payment")
     */
    public function index(): Response
    {
        $newPaymentMethod = new BankAccount();
        $newPaymentMethod->setEnabled(true);
        $newPaymentMethod->setAccountName("test");
        $newPaymentMethod->setIban("1234");
        $newPaymentMethod->setBic("COKS");
        $newPaymentMethod->setBankName("KSK");

        $em = $this->getDoctrine()->getManager();
        $em->persist($newPaymentMethod);
        $em->flush();
        return $this->render('payment/index.html.twig', [
            'controller_name' => 'PaymentController',
        ]);
    }
}
