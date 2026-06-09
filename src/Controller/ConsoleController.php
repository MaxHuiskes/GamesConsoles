<?php

namespace App\Controller;

use App\Entity\Console;
use App\Entity\User;
use App\Form\ConsoleType;
use App\Collection\Condition;
use App\Model\CollectionListQuery;
use App\Repository\BrandRepository;
use App\Repository\ConsoleRepository;
use App\Security\Voter\CollectionVoter;
use App\Service\DuplicateChecker;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/consoles')]
class ConsoleController extends AbstractController
{
    #[Route('', name: 'app_console_index', methods: ['GET'])]
    public function index(
        Request $request,
        ConsoleRepository $consoleRepository,
        BrandRepository $brandRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $listQuery = CollectionListQuery::fromRequest($request, withCondition: false);

        return $this->render('console/index.html.twig', [
            'consoles' => $consoleRepository->findByOwner($user, $listQuery),
            'list_query' => $listQuery,
            'brands' => $brandRepository->findByOwner($user),
            'condition_choices' => Condition::CHOICES,
        ]);
    }

    #[Route('/new', name: 'app_console_new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $entityManager,
        DuplicateChecker $duplicateChecker,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $console = new Console();
        $form = $this->createForm(ConsoleType::class, $console, ['owner' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $brand = $console->getBrand();
            if (null !== $brand) {
                $duplicate = $duplicateChecker->findDuplicateConsole(
                    $user,
                    $brand,
                    (string) $console->getName(),
                    null,
                    $request,
                );
                if (null !== $duplicate) {
                    return $this->render('console/new.html.twig', [
                        'console' => $console,
                        'form' => $form,
                        'duplicate' => $duplicate,
                    ]);
                }
            }

            $entityManager->persist($console);
            $entityManager->flush();

            return $this->redirectToRoute('app_console_show', ['id' => $console->getId()]);
        }

        return $this->render('console/new.html.twig', [
            'console' => $console,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_console_show', methods: ['GET'])]
    public function show(Console $console): Response
    {
        $this->denyAccessUnlessGranted(CollectionVoter::VIEW, $console);

        return $this->render('console/show.html.twig', [
            'console' => $console,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_console_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        Console $console,
        EntityManagerInterface $entityManager,
        DuplicateChecker $duplicateChecker,
    ): Response {
        $this->denyAccessUnlessGranted(CollectionVoter::EDIT, $console);

        /** @var User $user */
        $user = $this->getUser();

        $form = $this->createForm(ConsoleType::class, $console, ['owner' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $brand = $console->getBrand();
            if (null !== $brand) {
                $duplicate = $duplicateChecker->findDuplicateConsole(
                    $user,
                    $brand,
                    (string) $console->getName(),
                    $console->getId(),
                    $request,
                );
                if (null !== $duplicate) {
                    return $this->render('console/edit.html.twig', [
                        'console' => $console,
                        'form' => $form,
                        'duplicate' => $duplicate,
                    ]);
                }
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_console_show', ['id' => $console->getId()]);
        }

        return $this->render('console/edit.html.twig', [
            'console' => $console,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_console_delete', methods: ['POST'])]
    public function delete(Request $request, Console $console, EntityManagerInterface $entityManager): Response
    {
        $this->denyAccessUnlessGranted(CollectionVoter::EDIT, $console);

        if ($this->isCsrfTokenValid('delete'.$console->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($console);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_console_index');
    }
}
