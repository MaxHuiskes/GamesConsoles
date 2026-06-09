<?php

namespace App\Controller;

use App\Entity\Tag;
use App\Entity\User;
use App\Form\TagType;
use App\Repository\TagRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/tags')]
class TagController extends AbstractController
{
    #[Route('', name: 'app_tag_index', methods: ['GET'])]
    public function index(TagRepository $tagRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();
        $tagRepository->ensureDefaultsForOwner($user);

        return $this->render('tag/index.html.twig', [
            'tags' => $tagRepository->findByOwner($user),
        ]);
    }

    #[Route('/new', name: 'app_tag_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $tag = new Tag();
        $tag->setOwner($user);
        $form = $this->createForm(TagType::class, $tag);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($tag);
            $entityManager->flush();

            return $this->redirectToRoute('app_tag_index');
        }

        return $this->render('tag/new.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_tag_delete', methods: ['POST'])]
    public function delete(Request $request, Tag $tag, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if ($tag->getOwner()?->getId() !== $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        if ($this->isCsrfTokenValid('delete'.$tag->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($tag);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_tag_index');
    }
}
