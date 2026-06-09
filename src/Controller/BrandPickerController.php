<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Entity\User;
use App\Repository\BrandRepository;
use App\Repository\ConsoleRepository;
use App\Security\Voter\CollectionVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BrandPickerController extends AbstractController
{
    #[Route('/pick-brand', name: 'app_brand_picker')]
    public function index(BrandRepository $brandRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('brand_picker/index.html.twig', [
            'brands' => $brandRepository->findByOwner($user),
        ]);
    }

    #[Route('/pick-brand/{id}', name: 'app_brand_picker_consoles')]
    public function consoles(Brand $brand, ConsoleRepository $consoleRepository): Response
    {
        $this->denyAccessUnlessGranted(CollectionVoter::VIEW, $brand);

        /** @var User $user */
        $user = $this->getUser();

        return $this->render('brand_picker/consoles.html.twig', [
            'brand' => $brand,
            'consoles' => $consoleRepository->findByBrandForOwner($brand, $user),
        ]);
    }
}
