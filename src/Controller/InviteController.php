<?php

namespace App\Controller;

use App\Entity\Invite;
use App\Entity\User;
use App\Form\InviteType;
use App\Repository\InviteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[Route('/invites')]
class InviteController extends AbstractController
{
    #[Route('', name: 'app_invite_index', methods: ['GET'])]
    public function index(InviteRepository $inviteRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('invite/index.html.twig', [
            'invites' => $inviteRepository->findByInviter($user),
            'inviteExpiryDays' => $inviteRepository->getExpiryDays(),
        ]);
    }

    #[Route('/new', name: 'app_invite_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        UrlGeneratorInterface $urlGenerator,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $invite = new Invite();
        $invite->setInvitedBy($user);
        $form = $this->createForm(InviteType::class, $invite);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($invite);
            $entityManager->flush();

            $inviteUrl = $urlGenerator->generate(
                'app_register_invite',
                ['token' => $invite->getToken()],
                UrlGeneratorInterface::ABSOLUTE_URL
            );

            return $this->render('invite/created.html.twig', [
                'invite' => $invite,
                'inviteUrl' => $inviteUrl,
            ]);
        }

        return $this->render('invite/new.html.twig', [
            'form' => $form,
        ]);
    }
}
