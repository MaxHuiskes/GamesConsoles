<?php

namespace App\Repository;

use App\Entity\Tag;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Tag>
 */
class TagRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tag::class);
    }

    /** @return list<Tag> */
    public function findByOwner(User $owner): array
    {
        return $this->findBy(['owner' => $owner], ['name' => 'ASC']);
    }

    public function ensureDefaultsForOwner(User $owner): void
    {
        $existing = array_map(
            fn (Tag $tag) => $tag->getName(),
            $this->findByOwner($owner)
        );

        $em = $this->getEntityManager();

        foreach (Tag::DEFAULT_NAMES as $name) {
            if (in_array($name, $existing, true)) {
                continue;
            }

            $tag = new Tag();
            $tag->setOwner($owner);
            $tag->setName($name);
            $em->persist($tag);
        }

        $em->flush();
    }
}
