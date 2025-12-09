<?php

namespace BandwidthLib\Messaging\Models;

use BandwidthLib\Messaging\Models\Contracts\ArrayConvertible;
use BandwidthLib\Messaging\Models\Enums\RbmActionType;
use BandwidthLib\Messaging\Models\Traits\Builder;
use BandwidthLib\Utils\DateTimeHelper;
use Exception;
use JsonSerializable;

/**
 * A suggested action for the recipient that will be displayed
 * on a rich card or below the RBM message body content.
 */
class RbmAction implements JsonSerializable, ArrayConvertible
{
    use Builder;

    protected function __construct(
        protected ?RbmActionType $type = null,
        protected string $text = "",
        protected string $postbackData = "",
        protected string $phoneNumber = "",
        protected string $latitude = "",
        protected string $longitude = "",
        protected string $label = "",
        protected string $title = "",
        protected string $startTime = "",
        protected string $endTime = "",
        protected string $description = "",
        protected string $url = "",
    ) {}

    public static function fromArray(array $data): static
    {
        return new static(
            RbmActionType::from($data["type"]),
            $data["text"],
            $data["postbackData"] ?? "",
            $data["phoneNumber"] ?? "",
            $data["latitude"] ?? "",
            $data["longitude"] ?? "",
            $data["label"] ?? "",
            $data["title"] ?? "",
            $data["startTime"] ?? "",
            $data["endTime"] ?? "",
            $data["description"] ?? "",
            $data["url"] ?? "",
        );
    }

    public function type(RbmActionType $type): static
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Displayed text for user to click
     *
     * @throws Exception when text is longer than 25 characters
     */
    public function text(string $text): static
    {
        if (strlen($text) > 25) {
            throw new Exception(
                "RBM action/suggestion text must be 25 characters or less.",
            );
        }

        $this->text = $text;
        return $this;
    }

    /**
     * Base64 payload delivered to the webhook receiver when the action/suggestion is accessed.
     *
     * @throws Exception when post back data is longer than 2048 characters
     */
    public function postbackData(string $postbackData): static
    {
        if (strlen($postbackData) > 2048) {
            throw new Exception(
                "RBM action/suggestion post back data must be 2048 characters or less.",
            );
        }

        $this->postbackData = $postbackData;
        return $this;
    }

    /**
     * E164 format phone number to dial.
     */
    public function phoneNumber(string $phoneNumber): static
    {
        $this->phoneNumber = $phoneNumber;
        return $this;
    }

    public function latitude(string $latitude): static
    {
        $this->latitude = $latitude;
        return $this;
    }

    public function longitude(string $longitude): static
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * The location label for show location action/suggestion.
     *
     * @throws Exception when label is longer than 100 characters
     */
    public function label(string $label): static
    {
        if (strlen($label) > 100) {
            throw new Exception(
                "RBM action/suggestion to show location must have a label of 100 characters or less.",
            );
        }

        $this->label = $label;
        return $this;
    }

    /**
     * The title for create calendar event action/suggestion.
     *
     * @throws Exception when title is longer than 100 characters
     */
    public function title(string $title): static
    {
        if (strlen($title) > 100) {
            throw new Exception(
                "RBM action/suggestion to create calendar event must have a title of 100 characters or less.",
            );
        }

        $this->title = $title;
        return $this;
    }

    /**
     * The start time for create calendar event action/suggestion.
     *
     * @throws Exception when start time is not a valid ISO 8601 date time
     */
    public function startTime(string $startTime): static
    {
        if (!DateTimeHelper::validISO8601Date($startTime)) {
            throw new Exception(
                "RBM action/suggestion to create a calendar event must have valid ISO 8601 start date time.",
            );
        }

        $this->startTime = $startTime;
        return $this;
    }

    /**
     * The end time for create calendar event action/suggestion.
     *
     * @throws Exception when end time is not a valid ISO 8601 date time
     */
    public function endTime(string $endTime): static
    {
        if (!DateTimeHelper::validISO8601Date($endTime)) {
            throw new Exception(
                "RBM action/suggestion to create a calendar event must have valid ISO 8601 end date time.",
            );
        }

        $this->endTime = $endTime;
        return $this;
    }

    /**
     * The description for the create calendar event action/suggestion.
     *
     * @throws Exception when description is longer than 500 characters
     */
    public function description(string $description): static
    {
        if (strlen($description) > 500) {
            throw new Exception(
                "RBM action/suggestion to create calendar event must have a description of 500 characters or less.",
            );
        }

        $this->description = $description;
        return $this;
    }

    /**
     * The URL for the open URL action/suggestion.
     *
     * @throws Exception when URL is longer than 2048 characters
     */
    public function url(string $url): static
    {
        if (strlen($url) > 2048) {
            throw new Exception(
                "RBM action/suggestion to open a URL must have a URL that is 2048 characters or less.",
            );
        }

        $this->url = $url;
        return $this;
    }

    public static function reply(string $text, string $postbackData): static
    {
        return static::build()
            ->type(RbmActionType::Reply)
            ->text($text)
            ->postbackData($postbackData);
    }

    /**
     * @param string $phoneNumber E164 format phone number to dial.
     */
    public static function dialPhone(
        string $text,
        string $postbackData,
        string $phoneNumber,
    ): static {
        return static::build()
            ->type(RbmActionType::DialPhone)
            ->text($text)
            ->postbackData($postbackData)
            ->phoneNumber($phoneNumber);
    }

    public static function showLocation(
        string $text,
        string $postbackData,
        string $latitude,
        string $longitude,
        string $label = "",
    ): static {
        return static::build()
            ->type(RbmActionType::ShowLocation)
            ->text($text)
            ->postbackData($postbackData)
            ->latitude($latitude)
            ->longitude($longitude)
            ->label($label);
    }

    public static function createCalendarEvent(
        string $text,
        string $postbackData,
        string $title,
        string $startTime,
        string $endTime,
        string $description = "",
    ): static {
        return static::build()
            ->type(RbmActionType::CreateCalendarEvent)
            ->text($text)
            ->postbackData($postbackData)
            ->title($title)
            ->startTime($startTime)
            ->endTime($endTime)
            ->description($description);
    }

    public static function openUrl(
        string $text,
        string $postbackData,
        string $url,
    ): static {
        return static::build()
            ->type(RbmActionType::OpenUrl)
            ->text($text)
            ->postbackData($postbackData)
            ->url($url);
    }

    public static function requestLocation(
        string $text,
        string $postbackData,
    ): static {
        return static::build()
            ->type(RbmActionType::RequestLocation)
            ->text($text)
            ->postbackData($postbackData);
    }

    /**
     * @return mixed[]
     */
    protected function dialPhoneArray(): array
    {
        return ["phoneNumber" => $this->phoneNumber];
    }

    /**
     * @return mixed[]
     */
    protected function showLocationArray(): array
    {
        return [
            "latitude" => $this->latitude,
            "longitude" => $this->longitude,
            "label" => $this->label,
        ];
    }

    /**
     * @return mixed[]
     */
    public function createCalendarEventArray(): array
    {
        return [
            "title" => $this->title,
            "startTime" => $this->startTime,
            "endTime" => $this->endTime,
            "description" => $this->description,
        ];
    }

    /**
     * @return mixed[]
     */
    public function openUrlArray(): array
    {
        return ["url" => $this->url];
    }

    protected function validateDialPhone(): void
    {
        if (!$this->phoneNumber) {
            throw new Exception(
                "RBM action/suggestion to dial phone is missing phone number.",
            );
        }
    }

    protected function validateShowLocation(): void
    {
        if (!$this->latitude || !$this->longitude) {
            throw new Exception(
                "RBM action/suggestion to show location is missing latitude and/or longitude.",
            );
        }
    }

    protected function validateCreateCalendarEvent(): void
    {
        if (!$this->title || !$this->startTime || !$this->endTime) {
            throw new Exception(
                "RBM action/suggestion to create calendar event is missing title, start time, and/or end time.",
            );
        }
    }

    protected function validateOpenUrl(): void
    {
        if (!$this->url) {
            throw new Exception(
                "RBM action/suggestion to open URL is missing the URL.",
            );
        }
    }

    public function validate(): void
    {
        if (!$this->type || !$this->text || !$this->postbackData) {
            throw new Exception(
                "RBM action/suggestion is missing type, text, or post back value.",
            );
        }

        match ($this->type) {
            RbmActionType::DialPhone => $this->validateDialPhone(),
            RbmActionType::ShowLocation => $this->validateShowLocation(),
            RbmActionType::CreateCalendarEvent
                => $this->validateCreateCalendarEvent(),
            RbmActionType::OpenUrl => $this->validateOpenUrl(),
            default => null,
        };
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        $minimal = [
            "type" => $this->type,
            "text" => $this->text,
            "postbackData" => $this->postbackData,
        ];

        return match ($this->type) {
            RbmActionType::Reply, RbmActionType::RequestLocation => $minimal,
            RbmActionType::DialPhone => [
                ...$minimal,
                ...$this->dialPhoneArray(),
            ],
            RbmActionType::ShowLocation => [
                ...$minimal,
                ...$this->showLocationArray(),
            ],
            RbmActionType::CreateCalendarEvent => [
                ...$minimal,
                ...$this->createCalendarEventArray(),
            ],
            RbmActionType::OpenUrl => [...$minimal, ...$this->openUrlArray()],
        };
    }

    public function jsonSerialize(): array
    {
        $this->validate();

        return $this->toArray();
    }

    /**
     * @throws Exception
     * @param mixed[] $data
     * @return null|static
     */
    public static function fromArray(array $data): static
    {
        // TODO
    }

    /**
     * @param mixed[] $data
     * @return null|static
     */
    public static function tryFromArray(array $data): ?static
    {
        // TODO
    }
}
