<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Service\UserData;
use App\Service\UserService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{

    /**
     * @Route("/registration", name="registration")
     */
    public function registerNewUser(Request $request, UserService $userService): Response
    {
        $form = $this->createForm(UserType::class, new UserData());

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UserData $data */
            $data = $form->getData();

            $userService->storeUser($data);

            return $this->redirect($this->generateUrl('app_login'));
        }

        return $this->render('registration/registration.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
