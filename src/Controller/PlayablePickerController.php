<?php

namespace App\Controller;

use App\Collection\Condition;
use App\Entity\User;
use App\Model\PlayablePickerQuery;
use App\Repository\ConsoleRepository;
use App\Repository\GameVersionRepository;
use App\Security\Voter\CollectionVoter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class PlayablePickerController extends AbstractController
{
    #[Route('/pick-playable', name: 'app_playable_picker', methods: ['GET'])]
    public function index(
        Request $request,
        ConsoleRepository $consoleRepository,
        GameVersionRepository $gameVersionRepository,
    ): Response {
        /** @var User $user */
        $user = $this->getUser();

        $query = PlayablePickerQuery::fromRequest($request);
        $console = null;

        if (null !== $query->consoleId) {
            $console = $consoleRepository->find($query->consoleId);
            if (null === $console || !$this->isGranted(CollectionVoter::VIEW, $console)) {
                $console = null;
                $query = new PlayablePickerQuery(condition: $query->condition);
            }
        }

        $picked = $request->query->has('pick');
        $version = null;
        $matchCount = 0;

        if ($picked) {
            $matchCount = $gameVersionRepository->countPlayableForOwner($user, $query);
            if ($matchCount > 0) {
                $version = $gameVersionRepository->findRandomPlayableForOwner($user, $query);
            }
        }

        return $this->render('playable_picker/index.html.twig', [
            'consoles' => $consoleRepository->findByOwner($user),
            'query' => $query,
            'console' => $console,
            'picked' => $picked,
            'version' => $version,
            'match_count' => $matchCount,
            'condition_choices' => Condition::CHOICES,
        ]);
    }
}
