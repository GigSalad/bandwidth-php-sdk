<?php

namespace BandwidthLib\Messaging\Models\Enums;

enum MessageChannel: string
{
    case RBM = "RBM";
    case SMS = "SMS";
    case MMS = "MMS";
}
