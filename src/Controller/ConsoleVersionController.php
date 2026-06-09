<?php

namespace App\Controller;

use App\Entity\Console;
use App\Entity\ConsoleVersion;
use App\Form\ConsoleVersionType;
use App\Security\Voter\CollectionVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/consoles/{console}/versions')]
class ConsoleVersionController extends AbstractController
{
    #[Route('/new', name: 'app_console_version_new', methods: ['GET', 'POST'])]
    public function new(Request $request, Console $console, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(CollectionVoter::EDIT, $console);

        $version = new ConsoleVersion();
        $version->setConsole($console);
        $form = $this->createForm(ConsoleVersionType::class, $version);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleFotoUpload($form->get('fotoFile')->getData(), $version);
            $entityManager->persist($version);
            $entityManager->flush();

            return $this->redirectToRoute('app_console_show', ['id' => $console->getId()]);
        }

        return $this->render('console_version/new.html.twig', [
            'console' => $console,
            'version' => $version,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_console_version_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Console $console,
        ConsoleVersion $version,
        EntityManagerInterface $entityManager,
    ): Response {
        $this->denyAccessUnlessGranted(CollectionVoter::EDIT, $version);

        $form = $this->createForm(ConsoleVersionType::class, $version);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleFotoUpload($form->get('fotoFile')->getData(), $version);
            $entityManager->flush();

            return $this->redirectToRoute('app_console_show', ['id' => $console->getId()]);
        }

        return $this->render('console_version/edit.html.twig', [
            'console' => $console,
            'version' => $version,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_console_version_delete', methods: ['POST'])]
    public function delete(
        Request $request,
        Console $console,
        ConsoleVersion $version,
        EntityManagerInterface $entityManager,
    ): Response {
        $this->denyAccessUnlessGranted(CollectionVoter::EDIT, $version);

        if ($this->isCsrfTokenValid('delete'.$version->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($version);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_console_show', ['id' => $console->getId()]);
    }

    private function handleFotoUpload(?UploadedFile $file, ConsoleVersion $version): void
    {
        if ($file instanceof UploadedFile) {
            $version->setFoto(file_get_contents($file->getPathname()));
        }
    }
}
