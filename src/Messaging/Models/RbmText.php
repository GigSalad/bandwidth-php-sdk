<?php

namespace BandwidthLib\Messaging\Models;

use Exception;

class RbmText extends MultiChannelListItemContent
{
    protected function __construct(
        protected string $text = "",
        protected ?RbmActions $suggestions = null,
    ) {}

    public static function fromArray(array $data): static
    {
        return static::__construct($data["text"], $data["suggestions"]);
    }

    public function text(string $text): static
    {
        $this->text = $text;
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
        if (!$this->text) {
            throw new Exception("RBM text must have text.");
        }
    }
}
