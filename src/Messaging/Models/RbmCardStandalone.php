<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Enums\Alignment;
use BandwidthLib\Messaging\Models\Enums\Orientation;
use Exception;

class RbmCardStandalone extends MultiChannelListItemContent
{
    public function __construct(
        protected Orientation $orientation = Orientation::Vertical,
        protected Alignment $thumbnailImageAlignment = Alignment::Left,
        protected ?RbmCardContent $cardContent = null,
        protected ?RbmActions $suggestions = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            Orientation::from($data["orientation"]),
            Alignment::from($data["thumbnailImageAlignment"]),
            isset($data["cardContent"])
                ? RbmCardContent::fromArray($data["cardContent"])
                : null,
            isset($data["suggestions"])
                ? RbmActions::fromArray($data["suggestions"])
                : null,
        );
    }

    public function orientation(Orientation $orientation): static
    {
        $this->orientation = $orientation;
        return $this;
    }

    public function alignment(Alignment $alignment): static
    {
        $this->thumbnailImageAlignment = $alignment;
        return $this;
    }

    public function cardContent(RbmCardContent $cardContent): static
    {
        $this->cardContent = $cardContent;
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

    public function validate(): void
    {
        if (!$this->cardContent) {
            throw new Exception("RBM standalone card must have card content");
        }
    }
}
