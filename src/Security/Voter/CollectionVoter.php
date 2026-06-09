<?php

namespace App\Security\Voter;

use App\Entity\Brand;
use App\Entity\Console;
use App\Entity\ConsoleVersion;
use App\Entity\Game;
use App\Entity\GameVersion;
use App\Entity\User;
use App\Repository\FriendshipRepository;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/** @extends Voter<string, Brand|Console|ConsoleVersion|Game|GameVersion> */
class CollectionVoter extends Voter
{
    public const VIEW = 'VIEW';
    public const EDIT = 'EDIT';

    public function __construct(
        private readonly FriendshipRepository $friendshipRepository,
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        if (!in_array($attribute, [self::VIEW, self::EDIT], true)) {
            return false;
        }

        return $subject instanceof Brand
            || $subject instanceof Console
            || $subject instanceof ConsoleVersion
            || $subject instanceof Game
            || $subject instanceof GameVersion;
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof User) {
            return false;
        }

        $owner = $this->getOwner($subject);

        if ($owner === null) {
            return false;
        }

        if ($owner->getId() === $user->getId()) {
            return true;
        }

        if ($attribute === self::VIEW) {
            return $this->friendshipRepository->areFriends($user, $owner);
        }

        return false;
    }

    private function getOwner(Brand|Console|ConsoleVersion|Game|GameVersion $subject): ?User
    {
        if ($subject instanceof Brand) {
            return $subject->getOwner();
        }

        if ($subject instanceof Console) {
            return $subject->getBrand()?->getOwner();
        }

        if ($subject instanceof ConsoleVersion) {
            return $subject->getConsole()?->getBrand()?->getOwner();
        }

        if ($subject instanceof Game) {
            return $this->getOwnerFromConsoles($subject->getConsoles());
        }

        return $this->getOwnerFromConsoles($subject->getGame()?->getConsoles() ?? []);
    }

    /** @param iterable<Console> $consoles */
    private function getOwnerFromConsoles(iterable $consoles): ?User
    {
        foreach ($consoles as $console) {
            $owner = $console->getBrand()?->getOwner();
            if ($owner !== null) {
                return $owner;
            }
        }

        return null;
    }
}
