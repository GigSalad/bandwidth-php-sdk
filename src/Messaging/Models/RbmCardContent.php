<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Contracts\ArrayConvertible;
use BandwidthLib\Messaging\Models\Traits\Builder;
use BandwidthLib\Messaging\Models\Traits\FromArray;
use BandwidthLib\Messaging\Models\Traits\ToArray;
use Exception;
use JsonSerializable;

class RbmCardContent implements JsonSerializable, ArrayConvertible
{
    use Builder, FromArray, ToArray;

    protected function __construct(
        protected string $title = "",
        protected string $description = "",
        protected ?RbmMediaFile $media = null,
        protected ?RbmCardActions $suggestions = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            $data["title"],
            $data["description"],
            isset($data["media"])
                ? RbmMediaFile::fromArray($data["media"])
                : null,
            isset($data["suggestions"])
                ? RbmCardActions::fromArray($data["suggestions"])
                : null,
        );
    }

    public function title(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function description(string $description): static
    {
        $this->description = $description;
        return $this;
    }

    public function media(RbmMediaFile $media): static
    {
        $this->media = $media;
        return $this;
    }

    public function hasTitle(): bool
    {
        return !empty($this->title);
    }

    public function mediaHasHeight(): bool
    {
        return $this->media->hasHeight();
    }

    public function actions(RbmCardActions $actions): static
    {
        $this->suggestions = $actions;
        return $this;
    }

    public function withAction(RbmAction $action): static
    {
        if (!$this->suggestions) {
            $this->suggestions = RbmCardActions::new([$action]);
        } else {
            $this->suggestions->push($action);
        }

        return $this;
    }

    public function validate(): void
    {
        if (!$this->title && !$this->description && !$this->media) {
            throw new Exception(
                "RBM card content must have at least a title, description, or media",
            );
        }
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
