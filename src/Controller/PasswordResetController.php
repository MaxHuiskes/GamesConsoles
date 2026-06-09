<?php

namespace App\Controller;

use App\Entity\PasswordResetToken;
use App\Form\ResetPasswordFormType;
use App\Form\ResetPasswordRequestFormType;
use App\Repository\PasswordResetTokenRepository;
use App\Repository\UserRepository;
use App\Service\PasswordResetMailer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class PasswordResetController extends AbstractController
{
    private const TOKEN_TTL_HOURS = 1;

    #[Route('/reset-password', name: 'app_reset_password_request', methods: ['GET', 'POST'])]
    public function request(
        Request $request,
        UserRepository $userRepository,
        PasswordResetTokenRepository $tokenRepository,
        EntityManagerInterface $entityManager,
        PasswordResetMailer $passwordResetMailer,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $form = $this->createForm(ResetPasswordRequestFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $email = $form->get('email')->getData();
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user !== null) {
                $tokenRepository->invalidateActiveForUser($user);

                $resetToken = new PasswordResetToken(
                    $user,
                    new \DateTimeImmutable(sprintf('+%d hours', self::TOKEN_TTL_HOURS))
                );

                $entityManager->persist($resetToken);
                $entityManager->flush();

                $passwordResetMailer->send($resetToken);
            }

            return $this->redirectToRoute('app_reset_password_check_email');
        }

        return $this->render('reset_password/request.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/reset-password/check-email', name: 'app_reset_password_check_email', methods: ['GET'])]
    public function checkEmail(): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        return $this->render('reset_password/check_email.html.twig');
    }

    #[Route('/reset-password/reset/{token}', name: 'app_reset_password_reset', methods: ['GET', 'POST'])]
    public function reset(
        string $token,
        Request $request,
        PasswordResetTokenRepository $tokenRepository,
        UserPasswordHasherInterface $passwordHasher,
        EntityManagerInterface $entityManager,
    ): Response {
        if ($this->getUser()) {
            return $this->redirectToRoute('app_home');
        }

        $resetToken = $tokenRepository->findValidByToken($token);
        if ($resetToken === null) {
            return $this->render('reset_password/invalid.html.twig');
        }

        $form = $this->createForm(ResetPasswordFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $user = $resetToken->getUser();
            if ($user === null) {
                return $this->render('reset_password/invalid.html.twig');
            }

            $user->setPassword($passwordHasher->hashPassword(
                $user,
                $form->get('plainPassword')->getData()
            ));

            $resetToken->markUsed();
            $entityManager->flush();

            $this->addFlash('success', 'Your password has been reset. You can log in now.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('reset_password/reset.html.twig', [
            'form' => $form,
        ]);
    }
}
