<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Enums\Alignment;
use BandwidthLib\Messaging\Models\Enums\Orientation;
use Exception;

class RbmCardStandalone extends MultiChannelListItemContent
{
    public function __construct(
        protected Orientation $orientation = Orientation::Vertical,
        protected ?Alignment $thumbnailImageAlignment = null,
        protected ?RbmCardContent $cardContent = null,
        protected ?RbmActions $suggestions = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            $data["orientation"],
            $data["thumbnailImageAlignment"],
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

    public function alignment(?Alignment $alignment): static
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

    protected function validateAlignment(): void
    {
        $isValidAlignment =
            $this->orientation === Orientation::Horizontal
                ? $this->thumbnailImageAlignment !== null
                : $this->thumbnailImageAlignment === null;

        if (!$isValidAlignment) {
            throw new Exception(
                "RBM standalone card with orientation '{$this->orientation->value}' must have correct alignment",
            );
        }
    }

    /**
     * Depending on the card's orientation the content media must have
     * corresponding properties. That is, media must have height when
     * the card is in vertical orientation.
     */
    protected function validateMedia(): void
    {
        $mediaHasHeight = $this->cardContent->mediaHasHeight();

        $isMediaHeightValid = match ($this->orientation) {
            Orientation::Horizontal => !$mediaHasHeight,
            Orientation::Vertical => $mediaHasHeight,
        };

        if (!$isMediaHeightValid) {
            throw new Exception(
                "RBM standalone card with orientation '{$this->orientation->value}' must have correct content media",
            );
        }
    }

    /**
     * Depending on the card's orientation the content needs a title.
     */
    protected function validateTitle(): void
    {
        $isValidTitle =
            $this->orientation === Orientation::Horizontal
                ? $this->cardContent->hasTitle()
                : true;

        if (!$isValidTitle) {
            throw new Exception(
                "RBM standalone card with orientation '{$this->orientation->value}' must have a title",
            );
        }
    }

    public function validate(): void
    {
        if (!$this->cardContent) {
            throw new Exception("RBM standalone card must have card content");
        }

        $this->validateAlignment();
        $this->validateMedia();
        $this->validateTitle();
    }
}
