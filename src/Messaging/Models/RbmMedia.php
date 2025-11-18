<?php

namespace BandwidthLib\Messaging\Models;

use Exception;

class RbmMedia extends MultiChannelListItemContent
{
    protected function __construct(
        protected ?RbmMediaFile $media = null,
        protected ?RbmActions $suggestions = null,
    ) {}

    public function media(RbmMediaFile $media): static
    {
        $this->media = $media;
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

    protected function validateMedia(): void
    {
        if (!$this->media) {
            throw new Exception("RBM media must have a media file.");
        }

        if ($this->media->hasHeight()) {
            throw new Exception(
                "RBM media must have a media file with no height.",
            );
        }
    }

    public function validate(): void
    {
        $this->validateMedia();
    }
}
