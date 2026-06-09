<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\GameVersionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PickerController extends AbstractController
{
    #[Route('/pick', name: 'app_picker')]
    public function pick(GameVersionRepository $gameVersionRepository): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->render('picker/index.html.twig', [
            'version' => $gameVersionRepository->findRandomForOwner($user),
        ]);
    }
}
