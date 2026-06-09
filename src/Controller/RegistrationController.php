<?php

namespace App\Controller;

use App\Entity\Invite;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Repository\InviteRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register', methods: ['GET', 'POST'])]
    public function registerBootstrap(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        if ($userRepository->countUsers() > 0) {
            return $this->render('registration/invite_required.html.twig');
        }

        return $this->handleRegistration($request, $passwordHasher, $entityManager, null);
    }

    #[Route('/register/{token}', name: 'app_register_invite', methods: ['GET', 'POST'])]
    public function registerInvite(
        string $token,
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        InviteRepository $inviteRepository,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $invite = $inviteRepository->findValidByToken($token);
        if ($invite === null) {
            return $this->render('registration/invite_invalid.html.twig');
        }

        return $this->handleRegistration($request, $passwordHasher, $entityManager, $invite);
    }

    private function handleRegistration(
        Request $request,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
        ?Invite $invite,
    ): Response {
        $user = new User();
        if ($invite?->getEmail()) {
            $user->setEmail($invite->getEmail());
        }

        $form = $this->createForm(RegistrationFormType::class, $user, [
            'email_readonly' => $invite?->getEmail() !== null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($invite?->getEmail() && $user->getEmail() !== $invite->getEmail()) {
                $this->addFlash('error', 'This invite is for a different email address.');

                return $this->render('registration/register.html.twig', [
                    'form' => $form,
                    'invite' => $invite,
                ]);
            }

            $user->setPassword($passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            ));

            $entityManager->persist($user);

            if ($invite !== null) {
                $invite->markUsed($user);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_login');
        }

        return $this->render('registration/register.html.twig', [
            'form' => $form,
            'invite' => $invite,
        ]);
    }
}
