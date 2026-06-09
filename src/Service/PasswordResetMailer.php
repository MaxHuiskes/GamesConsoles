<?php

namespace App\Service;

use App\Entity\PasswordResetToken;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PasswordResetMailer
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly string $mailerFrom,
    ) {
    }

    public function send(PasswordResetToken $resetToken): void
    {
        $user = $resetToken->getUser();
        if ($user === null || $user->getEmail() === null) {
            return;
        }

        $resetUrl = $this->urlGenerator->generate(
            'app_reset_password_reset',
            ['token' => $resetToken->getToken()],
            UrlGeneratorInterface::ABSOLUTE_URL
        );

        $email = (new TemplatedEmail())
            ->from($this->mailerFrom)
            ->to($user->getEmail())
            ->subject('Reset your GamesConsoles password')
            ->htmlTemplate('email/reset_password.html.twig')
            ->context([
                'resetUrl' => $resetUrl,
                'expiresAt' => $resetToken->getExpiresAt(),
            ]);

        $this->mailer->send($email);
    }
}
