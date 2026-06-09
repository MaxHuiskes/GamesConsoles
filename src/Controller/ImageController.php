<?php

namespace App\Controller;

use App\Entity\ConsoleVersion;
use App\Entity\GameVersion;
use App\Security\Voter\CollectionVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ImageController extends AbstractController
{
    #[Route('/image/console-version/{id}', name: 'app_image_console_version')]
    public function consoleVersion(ConsoleVersion $version): Response
    {
        $this->denyAccessUnlessGranted(CollectionVoter::VIEW, $version);

        if (!$version->hasFoto()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return $this->blobResponse($version->getFoto());
    }

    #[Route('/image/game-version/{id}', name: 'app_image_game_version')]
    public function gameVersion(GameVersion $version): Response
    {
        $this->denyAccessUnlessGranted(CollectionVoter::VIEW, $version);

        if (!$version->hasFoto()) {
            return new Response('', Response::HTTP_NOT_FOUND);
        }

        return $this->blobResponse($version->getFoto());
    }

    private function blobResponse(mixed $blob): Response
    {
        if (is_resource($blob)) {
            $blob = stream_get_contents($blob);
        }

        $response = new Response((string) $blob);
        $response->headers->set('Content-Type', 'image/jpeg');

        return $response;
    }
}
