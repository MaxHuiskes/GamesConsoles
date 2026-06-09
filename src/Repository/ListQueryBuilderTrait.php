<?php

namespace App\Repository;

use App\Collection\Condition;
use App\Model\CollectionListQuery;
use Doctrine\ORM\QueryBuilder;

trait ListQueryBuilderTrait
{
    private function applyNameFilter(QueryBuilder $qb, string $alias, CollectionListQuery $query): void
    {
        if ('' === $query->q) {
            return;
        }

        $qb->andWhere(sprintf('LOWER(%s.name) LIKE :list_q', $alias))
            ->setParameter('list_q', '%'.mb_strtolower($query->q).'%');
    }

    private function applyBrandFilter(QueryBuilder $qb, string $brandAlias, CollectionListQuery $query): void
    {
        if (null === $query->brandId) {
            return;
        }

        $qb->andWhere(sprintf('%s.id = :list_brand', $brandAlias))
            ->setParameter('list_brand', $query->brandId);
    }

    private function applySort(QueryBuilder $qb, string $alias, CollectionListQuery $query, string $versionAlias): void
    {
        $direction = 'desc' === $query->dir ? 'DESC' : 'ASC';

        if (CollectionListQuery::SORT_PRIJS === $query->sort) {
            $qb->leftJoin(sprintf('%s.versions', $alias), $versionAlias);
            $qb->addSelect(sprintf('MIN(%s.prijs) AS HIDDEN list_min_prijs', $versionAlias));
            $qb->groupBy(sprintf('%s.id', $alias));
            $qb->orderBy('list_min_prijs', $direction);
            $qb->addOrderBy(sprintf('%s.name', $alias), 'ASC');

            return;
        }

        if (CollectionListQuery::SORT_CONDITION === $query->sort) {
            $qb->leftJoin(sprintf('%s.versions', $alias), $versionAlias);
            $qb->addSelect(sprintf('MIN(%s) AS HIDDEN list_condition_rank', Condition::sortRankDql($versionAlias)));
            $qb->groupBy(sprintf('%s.id', $alias));
            $qb->orderBy('list_condition_rank', $direction);
            $qb->addOrderBy(sprintf('%s.name', $alias), 'ASC');

            return;
        }

        $qb->orderBy(sprintf('%s.name', $alias), $direction);
    }
}
