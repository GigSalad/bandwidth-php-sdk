<?php

namespace BandwidthLib\Messaging\Models;

use Exception;

class Sms extends MultiChannelListItemContent
{
    protected string $text = "";

    /**
     * @throws Exception when text is longer than 2048 characters
     */
    public function text(string $text): static
    {
        if (strlen($text) > 2048) {
            throw new Exception(
                "RBM SMS item text must be 2048 characters or less.",
            );
        }

        $this->text = $text;
        return $this;
    }

    public function validate(): void
    {
        if (!$this->text) {
            throw new Exception("SMS must have text.");
        }
    }
}
