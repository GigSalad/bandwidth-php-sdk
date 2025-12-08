<?php

namespace BandwidthLib\Messaging\Models;

use Exception;

/**
 * Regarding the media for MMS, the Bandwidth API limits file size to
 * 3.5MB. Specific carriers and channels may have a smaller limit that
 * could cause a large file to fail.
 *
 * See: https://support.bandwidth.com/hc/en-us/articles/360014235473-What-are-the-MMS-file-size-limits
 */
class Mms extends MultiChannelListItemContent
{
    /**
     * @param string|MmsMediaFile[] $media
     */
    protected function __construct(
        protected string $text = "",
        protected array|string|MmsMediaFile $media = [],
    ) {
        if ($media) {
            $this->media = static::intoMedia($media);
        }
    }

    public static function fromArray(array $data): static
    {
        return static::__construct($data["text"] ?? "", $data["media"] ?? []);
    }

    /**
     * @param string|MmsMediaFile|MmsMediaFile[] $media
     * @return MmsMediaFile[]
     */
    protected static function intoMedia(array|string|MmsMediaFile $media): array
    {
        return match (true) {
            is_string($media) => [MmsMediaFile::new($media)],
            $media instanceof MmsMediaFile => [$media],
            default => $media,
        };
    }

    /**
     * @throws Exception when text is longer than 2048 characters
     */
    public function text(string $text): static
    {
        if (strlen($text) > 2048) {
            throw new Exception(
                "RBM MMS item text must be 2048 characters or less.",
            );
        }

        $this->text = $text;
        return $this;
    }

    /**
     * Assign media by overwriting what may be set already.
     *
     * @param string|MmsMediaFile|MmsMediaFile[] $media
     */
    public function media(array|string|MmsMediaFile $media): static
    {
        $this->media = static::intoMedia($media);
        return $this;
    }

    /**
     * Add a single media item.
     *
     * @param string|MmsMediaFile $media A media URL or media file object
     */
    public function withMedia(string|MmsMediaFile $media): static
    {
        $this->media[] = is_string($media) ? MmsMediaFile::new($media) : $media;
        return $this;
    }

    protected function validateMedia(): void
    {
        if (!\is_array($this->media)) {
            throw new Exception("MMS media must be an array");
        }

        $invalidMedia = array_filter(
            $this->media,
            fn(mixed $mediaItem) => !($mediaItem instanceof MmsMediaFile),
        );

        if (!empty($invalidMedia)) {
            throw new Exception("MMS media must all be `MmsMediaFile` type");
        }
    }

    public function validate(): void
    {
        if (!trim($this->text) && empty($this->media)) {
            throw new Exception("MMS must have text, media, or both");
        }

        $this->validateMedia();
    }
}
