<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\CollectionImportType;
use App\Service\CollectionExportService;
use App\Service\CollectionImportService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/collection')]
class CollectionController extends AbstractController
{
    #[Route('', name: 'app_collection', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        CollectionImportService $collectionImportService,
    ): Response {
        $form = $this->createForm(CollectionImportType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var User $user */
            $user = $this->getUser();

            $file = $form->get('file')->getData();
            $contents = file_get_contents($file->getPathname());
            if (false === $contents) {
                $this->addFlash('error', 'Could not read uploaded file.');

                return $this->redirectToRoute('app_collection');
            }

            try {
                $result = str_ends_with(strtolower($file->getClientOriginalName()), '.csv')
                    ? $collectionImportService->importCsv($user, $contents)
                    : $collectionImportService->importJson($user, $contents);

                $this->addFlash(
                    'success',
                    sprintf('Import finished. Created %d items, skipped %d.', $result['created'], $result['skipped']),
                );
            } catch (\Throwable $exception) {
                $this->addFlash('error', 'Import failed: '.$exception->getMessage());
            }

            return $this->redirectToRoute('app_collection');
        }

        return $this->render('collection/index.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/export.json', name: 'app_collection_export_json', methods: ['GET'])]
    public function exportJson(CollectionExportService $collectionExportService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $response = new Response($collectionExportService->toJson($user));
        $response->headers->set('Content-Type', 'application/json');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'gamesconsoles-collection.json',
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }

    #[Route('/export.csv', name: 'app_collection_export_csv', methods: ['GET'])]
    public function exportCsv(CollectionExportService $collectionExportService): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $response = new Response($collectionExportService->toCsv($user));
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $disposition = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'gamesconsoles-collection.csv',
        );
        $response->headers->set('Content-Disposition', $disposition);

        return $response;
    }
}
