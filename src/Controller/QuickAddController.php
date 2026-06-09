<?php

namespace App\Controller;

use App\Entity\Console;
use App\Entity\Game;
use App\Entity\User;
use App\Form\QuickAddType;
use App\Model\QuickAddData;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class QuickAddController extends AbstractController
{
    #[Route('/quick-add', name: 'app_quick_add', methods: ['GET', 'POST'])]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        $data = new QuickAddData();
        $form = $this->createForm(QuickAddType::class, $data, ['owner' => $user]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (QuickAddData::TYPE_CONSOLE === $data->type) {
                if (null === $data->brand) {
                    $this->addFlash('error', 'Choose a brand for the console.');

                    return $this->render('quick_add/index.html.twig', ['form' => $form]);
                }

                $console = new Console();
                $console->setBrand($data->brand);
                $console->setName(trim($data->name));
                $data->brand->addConsole($console);
                $entityManager->persist($console);
                $entityManager->flush();

                return $this->redirectToRoute('app_console_show', ['id' => $console->getId()]);
            }

            $game = new Game();
            $game->setOwner($user);
            $game->setName(trim($data->name));
            if ($data->console instanceof Console) {
                $game->addConsole($data->console);
            }
            $entityManager->persist($game);
            $entityManager->flush();

            return $this->redirectToRoute('app_game_show', ['id' => $game->getId()]);
        }

        return $this->render('quick_add/index.html.twig', [
            'form' => $form,
        ]);
    }
}
