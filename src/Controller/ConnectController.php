<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\FriendshipRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Http\Util\TargetPathTrait;

class ConnectController extends AbstractController
{
    use TargetPathTrait;

    #[Route('/connect/{token}', name: 'app_connect')]
    public function connect(
        string $token,
        Request $request,
        UserRepository $userRepository,
        FriendshipRepository $friendshipRepository,
    ): Response {
        $target = $this->generateUrl('app_connect', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

        if (!$this->getUser()) {
            $this->saveTargetPath($request->getSession(), 'main', $target);

            return $this->redirectToRoute('app_login');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        $otherUser = $userRepository->findByConnectToken($token);
        if ($otherUser === null) {
            return $this->render('connect/invalid.html.twig');
        }

        if ($otherUser->getId() === $currentUser->getId()) {
            return $this->render('connect/self.html.twig');
        }

        $friendshipRepository->connect($currentUser, $otherUser);

        $this->addFlash('success', sprintf('You are now connected with %s.', $otherUser->getEmail()));

        return $this->redirectToRoute('app_friend_show', ['id' => $otherUser->getId()]);
    }
}
