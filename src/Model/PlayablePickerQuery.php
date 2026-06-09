<?php

namespace App\Model;

use App\Collection\Condition;
use App\Http\RequestQuery;
use Symfony\Component\HttpFoundation\Request;

final class PlayablePickerQuery
{
    public function __construct(
        public readonly ?int $consoleId = null,
        public readonly ?string $condition = null,
    ) {
    }

    public function hasFilters(): bool
    {
        return null !== $this->consoleId || null !== $this->condition;
    }

    /** @return array<string, string> */
    public function queryParams(bool $includePick = true): array
    {
        $params = [];

        if (null !== $this->consoleId) {
            $params['console'] = (string) $this->consoleId;
        }
        if (null !== $this->condition) {
            $params['condition'] = $this->condition;
        }
        if ($includePick) {
            $params['pick'] = '1';
        }

        return $params;
    }

    public static function fromRequest(Request $request): self
    {
        $consoleId = RequestQuery::optionalPositiveInt($request, 'console');

        $rawCondition = $request->query->getString('condition');
        $condition = Condition::isValid($rawCondition) ? $rawCondition : null;

        return new self($consoleId, $condition);
    }
}
