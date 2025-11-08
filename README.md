# Bandwidth PHP SDK

---

## GigSalad fork

Support added for sending [RBM/RCS messages via Multi-Channel API](https://dev.bandwidth.com/apis/messaging-apis/messaging/#tag/Multi-Channel/operation/createMultiChannelMessage).

See [example](#rbmrcs-messages) below.

---

[![Test](https://github.com/Bandwidth/php-sdk/actions/workflows/test.yml/badge.svg)](https://github.com/Bandwidth/php-sdk/actions/workflows/test.yml)

|    **OS**    |      **PHP**       |
| :----------: | :----------------: |
| Windows 2022 | 8.0, 8.1, 8.2, 8.3 |
| Windows 2025 | 8.0, 8.1, 8.2, 8.3 |
| Ubuntu 22.04 | 8.0, 8.1, 8.2, 8.3 |
| Ubuntu 24.04 | 8.0, 8.1, 8.2, 8.3 |

## Getting Started

### Installation

```
composer require bandwidth/sdk
```

### Initialize

```php

require "vendor/autoload.php";

$config = new BandwidthLib\Configuration(
    array(
        'messagingBasicAuthUserName' => 'username',
        'messagingBasicAuthPassword' => 'password',
        'voiceBasicAuthUserName' => 'username',
        'voiceBasicAuthPassword' => 'password',
        'twoFactorAuthBasicAuthUserName' => 'username',
        'twoFactorAuthBasicAuthPassword' => 'password',
        'webRtcBasicAuthUserName' => 'username',
        'webRtcBasicAuthPassword' => 'password'
    )
);
$client = new BandwidthLib\BandwidthClient($config);
$accountId = "12345";
```

### Create A Phone Call

```php

$voiceClient = $client->getVoice()->getClient();

$body = new BandwidthLib\Voice\Models\CreateCallRequest();
$body->from = "+15554443333";
$body->to = "+15554442222";
$body->answerUrl = "https://test.com";
$body->applicationId = "3-d-4-b-5";

try {
    $response = $voiceClient->createCall($voiceAccountId, $body);
    print_r($response);
} catch (Exception $e) {
    print_r($e);
}
```

### Send A Text Message

```php

$messagingClient = $client->getMessaging()->getClient();

$body = new BandwidthLib\Messaging\Models\MessageRequest();
$body->from = "+12345678901";
$body->to = array("+12345678902");
$body->applicationId = "1234-ce-4567-de";
$body->text = "Greetings!";

try {
    $response = $messagingClient->createMessage($messagingAccountId, $body);
    print_r($response);
} catch (Exception $e) {
    print_r($e);
}
```

### RBM/RCS messages

See multi-channel documentation: https://dev.bandwidth.com/apis/messaging-apis/messaging/#tag/Multi-Channel

Core models to use for building different RBM types:

```php
use BandwidthLib\Messaging\Models\Mms;
use BandwidthLib\Messaging\Models\RbmCardCarousel;
use BandwidthLib\Messaging\Models\RbmCardStandalone;
use BandwidthLib\Messaging\Models\RbmMedia;
use BandwidthLib\Messaging\Models\RbmText;
use BandwidthLib\Messaging\Models\Sms;
```

Additional RBM helper and abstraction models:

```php
use BandwidthLib\Messaging\Models\MmsMediaFile;
use BandwidthLib\Messaging\Models\RbmAction;
use BandwidthLib\Messaging\Models\RbmActions;
use BandwidthLib\Messaging\Models\RbmCardActions;
use BandwidthLib\Messaging\Models\RbmCardContent;
use BandwidthLib\Messaging\Models\RbmMediaFile;
```

Basic text RBM:

```php
use BandwidthLib\Messaging\Models\MultiChannelMessageRequest;
use BandwidthLib\Messaging\Models\RbmText;

$messagingClient = $client->getMessaging()->getClient();

$messagingAccountId = "1234";
$to = "+12345678901";
$from = "RBM_SENDER";
$applicationId = "1234-ce-4567-de";

$content = RbmText::build()
    ->text("This is a test RBM message!");

$body = MultiChannelMessageRequest::rbm($to, $from, $applicationId, $content);

try {
    $response = $client->createMultiChannelMessage($messagingAccountId, $body);
    var_dump($response);
} catch (Exception $e) {
    var_dump($e);
}
```

Single card with actions RBM and fall back to SMS:

```php
use BandwidthLib\Messaging\Models\MultiChannelMessageRequest;
use BandwidthLib\Messaging\Models\RbmCardStandalone;
use BandwidthLib\Messaging\Models\RbmCardContent;
use BandwidthLib\Messaging\Models\RbmCardActions;
use BandwidthLib\Messaging\Models\Sms;

$messagingClient = $client->getMessaging()->getClient();

$messagingAccountId = "1234";
$to = "+12345678901";
$rbmFrom = "RBM_SENDER";
$applicationId = "1234-ce-4567-de";

$rbmCard = RbmCardStandalone::build()
    ->cardContent(RbmCardContent::build()
        ->title("Card title")
        ->description("A body of text within the card.")
        ->actions(RbmCardActions::build()
            ->withShowLocation("Visit us", "base64 post back data", "35.220368347871265", "-80.8427683629851", "Charlotte, NC")
            ->withDialPhone("Give us a call", "base64 post back data", "+12345678901")
            ->withOpenUrl("Check our website", "base64 post back data", "https://some-url-here.com")
            ->withReply("Got it", "base64 post back data")));

$sms = Sms::build()
    ->text("SMS fall back message");

$smsFrom = "+12345678901";

$body = MultiChannelMessageRequest::rbm($to, $rbmFrom, $applicationId, $rbmCard)
    ->withSms($smsFrom, $applicationId, $sms);

try {
    $response = $client->createMultiChannelMessage($messagingAccountId, $body);
    var_dump($response);
} catch (Exception $e) {
    var_dump($e);
}
```

### Create BXML

```php

$speakSentence = BandwidthLib\Voice\Bxml\SpeakSentence::make("Hello!")
    ->voice("susan")
    ->locale("en_US")
    ->gender("female");
$response = BandwidthLib\Voice\Bxml\Response::make()
    ->addVerb($speakSentence);
echo $response->toBxml();
```

### Create A MFA Request

```php

$mfaClient = $client->getTwoFactorAuth()->getMFA();

$body = new BandwidthLib\TwoFactorAuth\Models\TwoFactorCodeRequestSchema();
$body->from = "+15554443333";
$body->to = "+15553334444";
$body->applicationId = "3-a-b-d";
$body->scope = "scope";
$body->digits = 6;
$body->message = "Your temporary {NAME} {SCOPE} code is {CODE}";
$mfaClient->createVoiceTwoFactor($accountId, $body);

$body = new BandwidthLib\TwoFactorAuth\Models\TwoFactorVerifyRequestSchema();
$body->from = "+15554443333";
$body->to = "+15553334444";
$body->applicationId = "3-a-b-d";
$body->scope = "scope";
$body->code = "123456";
$body->digits = 6;
$body->expirationTimeInMinutes = 3;

$response = $mfaClient->createVerifyTwoFactor($accountId, $body);
echo $response->getResult()->valid;
```

### WebRtc Participant & Session Management

```php

$webRtcClient = $client->getWebRtc()->getClient();

$createSessionBody = new BandwidthLib\WebRtc\Models\Session();
$createSessionBody->tag = 'new-session';

$createSessionResponse = $webRtcClient->createSession($accountId, $createSessionBody);
$sessionId = $createSessionResponse->getResult()->id;

$createParticipantBody = new BandwidthLib\WebRtc\Models\Participant();
$createParticipantBody->callbackUrl = 'https://sample.com';
$createParticipantBody->publishPermissions = array(
    BandwidthLib\WebRtc\Models\PublishPermissionEnum::AUDIO,
    BandwidthLib\WebRtc\Models\PublishPermissionEnum::VIDEO
);

$body = new BandwidthLib\WebRtc\Models\Subscriptions();
$body->sessionId = "1234-abcd";

$createParticipantResponse = $webRtcClient->createParticipant($accountId, $createParticipantBody);
$participantId = $createParticipantResponse->getResult()->participant->id;

$webRtcClient->addParticipantToSession($accountId, $sessionId, $participantId, $body);
```

## Supported PHP Versions

This package can be used with PHP >= 7.2

## Documentation

Documentation for this package can be found at [https://dev.bandwidth.com/sdks/php/](https://dev.bandwidth.com/sdks/php/)

## Credentials

Information for credentials for this package can be found at https://dev.bandwidth.com/guides/accountCredentials.html
