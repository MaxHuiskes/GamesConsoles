<?php

namespace App\Model;

use App\Collection\Condition;
use Symfony\Component\HttpFoundation\Request;

final class CollectionListQuery
{
    public const SORT_NAME = 'name';
    public const SORT_PRIJS = 'prijs';
    public const SORT_CONDITION = 'condition';

    public function __construct(
        public readonly string $q = '',
        public readonly ?int $brandId = null,
        public readonly ?int $tagId = null,
        public readonly ?string $condition = null,
        public readonly string $sort = self::SORT_NAME,
        public readonly string $dir = 'asc',
    ) {
    }

    public function hasFilters(): bool
    {
        return '' !== $this->q
            || null !== $this->brandId
            || null !== $this->tagId
            || null !== $this->condition;
    }

    public function isDefaultSort(): bool
    {
        return self::SORT_NAME === $this->sort && 'asc' === $this->dir;
    }

    /** @return array<string, string> */
    public function queryParams(): array
    {
        $params = [];

        if ('' !== $this->q) {
            $params['q'] = $this->q;
        }
        if (null !== $this->brandId) {
            $params['brand'] = (string) $this->brandId;
        }
        if (null !== $this->tagId) {
            $params['tag'] = (string) $this->tagId;
        }
        if (null !== $this->condition) {
            $params['condition'] = $this->condition;
        }
        if (!$this->isDefaultSort()) {
            $params['sort'] = $this->sort;
            $params['dir'] = $this->dir;
        }

        return $params;
    }

    public static function fromRequest(
        Request $request,
        bool $withTag = false,
        bool $withCondition = false,
        bool $withExtendedSort = true,
    ): self {
        $q = trim($request->query->getString('q'));

        $brandId = $request->query->getInt('brand');
        $brandId = $brandId > 0 ? $brandId : null;

        $tagId = null;
        if ($withTag) {
            $tagId = $request->query->getInt('tag');
            $tagId = $tagId > 0 ? $tagId : null;
        }

        $condition = null;
        if ($withCondition) {
            $rawCondition = $request->query->getString('condition');
            $condition = Condition::isValid($rawCondition) ? $rawCondition : null;
        }

        $allowedSort = [self::SORT_NAME];
        if ($withExtendedSort) {
            $allowedSort[] = self::SORT_PRIJS;
            $allowedSort[] = self::SORT_CONDITION;
        }

        $sort = $request->query->getString('sort', self::SORT_NAME);
        if (!in_array($sort, $allowedSort, true)) {
            $sort = self::SORT_NAME;
        }

        $dir = strtolower($request->query->getString('dir', 'asc'));
        if (!in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'asc';
        }

        return new self($q, $brandId, $tagId, $condition, $sort, $dir);
    }
}
