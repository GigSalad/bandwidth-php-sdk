<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Enums\RbmCardCarouselWidth;
use Exception;

class RbmCardCarousel extends MultiChannelListItemContent
{
    public const int LIMIT = 10;

    /**
     * @param RbmCardContent[] $cardContents
     * @throws Exception when more than the limit of cards are added
     */
    public function __construct(
        protected RbmCardCarouselWidth $cardWidth = RbmCardCarouselWidth::Small,
        protected array $cardContents = [],
        protected ?RbmActions $suggestions = null,
    ) {
        if ($this->count() > static::LIMIT) {
            $this->throwTooManyCardsException();
        }
    }

    public static function fromArray(array $data): static
    {
        return new static(
            $data["cardWidth"],
            static::contentsFromArray($data["cardContents"] ?? []),
            $data["suggestions"] ?? null,
        );
    }

    /**
     * @param mixed[] $data
     * @return RbmCardContent[]
     */
    private static function contentsFromArray(array $data): array
    {
        return array_map(RbmCardContent::fromArray(...), $data);
    }

    private function throwTooManyCardsException(): void
    {
        $limit = static::LIMIT;

        throw new Exception(
            "RBM card carousel cannot have over {$limit} cards",
        );
    }

    public function cardWidth(RbmCardCarouselWidth $cardWidth): static
    {
        $this->cardWidth = $cardWidth;
        return $this;
    }

    public function actions(RbmActions $actions): static
    {
        $this->suggestions = $actions;
        return $this;
    }

    public function withAction(RbmAction $action): static
    {
        if (!$this->suggestions) {
            $this->suggestions = RbmActions::new([$action]);
        } else {
            $this->suggestions->push($action);
        }

        return $this;
    }

    /**
     * @throws Exception when attempting to add another item when at the limit
     */
    public function push(RbmCardContent $cardContent): void
    {
        if ($this->isFull()) {
            $this->throwTooManyCardsException();
        }

        $this->cardContents[] = $cardContent;
    }

    public function count(): int
    {
        return count($this->cardContents);
    }

    public function isEmpty(): bool
    {
        return empty($this->cardContents);
    }

    public function isFull(): bool
    {
        return $this->count() >= static::LIMIT;
    }

    protected function validateMedia(): void
    {
        $contentsWithoutMediaHeight = array_filter(
            $this->cardContents,
            fn(RbmCardContent $cardContent) => !$cardContent->mediaHasHeight(),
        );

        if (!empty($contentsWithoutMediaHeight)) {
            throw new Exception(
                "RBM card carousel contents must all have media with height",
            );
        }
    }

    public function validate(): void
    {
        if ($this->isEmpty()) {
            throw new Exception("RBM card carousel cannot be empty");
        }

        $this->validateMedia();
    }
}
