<?php

namespace BandwidthLib\Messaging\Models\Enums;

enum MessagePriority: string
{
    case Default = "default";
    case High = "high";
}
