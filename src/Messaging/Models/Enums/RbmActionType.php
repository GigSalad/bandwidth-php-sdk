<?php

namespace BandwidthLib\Messaging\Models\Enums;

enum RbmActionType: string
{
    case Reply = "REPLY";
    case DialPhone = "DIAL_PHONE";
    case ShowLocation = "SHOW_LOCATION";
    case CreateCalendarEvent = "CREATE_CALENDAR_EVENT";
    case OpenUrl = "OPEN_URL";
    case RequestLocation = "REQUEST_LOCATION";
}
