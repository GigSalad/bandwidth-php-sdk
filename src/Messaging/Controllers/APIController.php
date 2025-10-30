<?php
namespace BandwidthLib\Messaging\Controllers;

use BandwidthLib\APIException;
use BandwidthLib\APIHelper;
use BandwidthLib\Messaging\Exceptions\MessagingException;
use BandwidthLib\Messaging\Models\MessageRequest;
use BandwidthLib\Messaging\Models\MultiChannelMessageRequest;
use BandwidthLib\Controllers\BaseController;
use BandwidthLib\Http\ApiResponse;
use BandwidthLib\Http\HttpRequest;
use BandwidthLib\Http\HttpResponse;
use BandwidthLib\Http\HttpMethod;
use BandwidthLib\Http\HttpContext;
use BandwidthLib\Servers;
use Unirest\Request;

class APIController extends BaseController
{
    public function __construct($config, $httpCallBack = null)
    {
        parent::__construct($config, $httpCallBack);
    }

    protected function validateResponse(
        HttpResponse $response,
        HttpContext $context,
    ): void {
        $this->throwSpecificResponses($response->getStatusCode(), $context);

        parent::validateResponse($response, $context);
    }

    /**
     * Error handling for some specific HTTP response codes.
     *
     * @throws \Exception when response code is certain non-200 values
     */
    protected function throwSpecificResponses(
        int $code,
        HttpContext $context,
    ): void {
        $message = match ($code) {
            400 => "400 Request is malformed or invalid",
            401 => "401 The specified user does not have access to the account",
            403 => "403 The user does not have access to this API",
            404 => "404 Path not found",
            415 => "415 The content-type of the request is incorrect",
            429 => "429 The rate limit has been reached",
            default => null,
        };

        if ($message !== null) {
            throw new MessagingException($message, $context);
        }
    }

    /**
     * @param array<string, string> $headers
     */
    protected function makeRequest(
        string $method,
        array $headers,
        string $url,
    ): HttpRequest {
        Request::auth(
            $this->config->getMessagingBasicAuthUserName(),
            $this->config->getMessagingBasicAuthPassword(),
        );

        Request::timeout($this->config->getTimeout());

        return new HttpRequest($method, $headers, $url);
    }

    /**
     * Gets a list of your media files. No query parameters are supported.
     *
     * @param string $accountId User's account ID
     * @param string|null $continuationToken (optional) Continuation token used to retrieve subsequent media.
     * @return ApiResponse response from the API call
     * @throws APIException Thrown if API call fails
     */
    public function listMedia(
        string $accountId,
        ?string $continuationToken = null,
    ) {
        $url = "/users/{$accountId}/media";

        $url = APIHelper::cleanUrl(
            $this->config->getBaseUri(Servers::MESSAGINGDEFAULT) . $url,
        );

        $_headers = [
            "user-agent" => BaseController::USER_AGENT,
            "Accept" => "application/json",
            "Continuation-Token" => $continuationToken,
        ];

        $_httpRequest = $this->makeRequest(HttpMethod::GET, $_headers, $url);

        $this->getHttpCallBack()?->callOnBeforeRequest($_httpRequest);

        // and invoke the API call request to fetch the response
        $response = Request::get($url, $_headers);

        $_httpResponse = new HttpResponse(
            $response->code,
            $response->headers,
            $response->raw_body,
        );
        $_httpContext = new HttpContext($_httpRequest, $_httpResponse);

        $this->getHttpCallBack()?->callOnAfterRequest($_httpContext);

        $this->validateResponse($_httpResponse, $_httpContext);

        $mapper = $this->getJsonMapper();
        $deserializedResponse = $mapper->mapClassArray(
            $response->body,
            "BandwidthLib\\Messaging\\Models\\Media",
        );
        return new ApiResponse(
            $response->code,
            $response->headers,
            $deserializedResponse,
        );
    }

    /**
     * Downloads a media file you previously uploaded.
     *
     * @param string $accountId User's account ID
     * @param string $mediaId   Media ID to retrieve
     * @return ApiResponse response from the API call
     * @throws APIException Thrown if API call fails
     */
    public function getMedia(string $accountId, string $mediaId)
    {
        $url = "/users/{$accountId}/media/{$mediaId}";

        $url = APIHelper::cleanUrl(
            $this->config->getBaseUri(Servers::MESSAGINGDEFAULT) . $url,
        );

        $_headers = [
            "user-agent" => BaseController::USER_AGENT,
        ];

        $_httpRequest = $this->makeRequest(HttpMethod::GET, $_headers, $url);

        $this->getHttpCallBack()?->callOnBeforeRequest($_httpRequest);

        // and invoke the API call request to fetch the response
        $response = Request::get($url, $_headers);

        $_httpResponse = new HttpResponse(
            $response->code,
            $response->headers,
            $response->raw_body,
        );
        $_httpContext = new HttpContext($_httpRequest, $_httpResponse);

        $this->getHttpCallBack()?->callOnAfterRequest($_httpContext);

        $this->validateResponse($_httpResponse, $_httpContext);

        $deserializedResponse = $response->body;
        return new ApiResponse(
            $response->code,
            $response->headers,
            $deserializedResponse,
        );
    }

    /**
     * Uploads a file the normal HTTP way. You may add headers to the request in order to provide some
     * control to your media-file.
     *
     * @param string $accountId     User's account ID
     * @param string $mediaId       The user supplied custom media ID
     * @param string $body
     * @param string $contentType   (optional) The media type of the entity-body
     * @param string|null $cacheControl  (optional) General-header field is used to specify directives that MUST be obeyed
     *                              by all caching mechanisms along the request/response chain.
     * @return ApiResponse response from the API call
     * @throws APIException Thrown if API call fails
     */
    public function uploadMedia(
        string $accountId,
        string $mediaId,
        string $body,
        string $contentType = "application/octet-stream",
        ?string $cacheControl = null,
    ) {
        $url = "/users/{$accountId}/media/{$mediaId}";

        $url = APIHelper::cleanUrl(
            $this->config->getBaseUri(Servers::MESSAGINGDEFAULT) . $url,
        );

        $_headers = [
            "user-agent" => BaseController::USER_AGENT,
            "Content-Type" =>
                null != $contentType
                    ? $contentType
                    : "application/octet-stream",
            "Cache-Control" => $cacheControl,
        ];

        $_bodyJson = $body;

        $_httpRequest = $this->makeRequest(HttpMethod::PUT, $_headers, $url);

        $this->getHttpCallBack()?->callOnBeforeRequest($_httpRequest);

        // and invoke the API call request to fetch the response
        $response = Request::put($url, $_headers, $_bodyJson);

        $_httpResponse = new HttpResponse(
            $response->code,
            $response->headers,
            $response->raw_body,
        );
        $_httpContext = new HttpContext($_httpRequest, $_httpResponse);

        $this->getHttpCallBack()?->callOnAfterRequest($_httpContext);

        $this->validateResponse($_httpResponse, $_httpContext);

        return new ApiResponse($response->code, $response->headers, null);
    }

    /**
     * Deletes a media file from Bandwidth API server. Make sure you don't have any application scripts
     * still using the media before you delete. If you accidentally delete a media file, you can
     * immediately upload a new file with the same name.
     *
     * @param string $accountId User's account ID
     * @param string $mediaId   The media ID to delete
     * @return ApiResponse response from the API call
     * @throws APIException Thrown if API call fails
     */
    public function deleteMedia(string $accountId, string $mediaId)
    {
        $url = "/users/{$accountId}/media/{$mediaId}";

        $url = APIHelper::cleanUrl(
            $this->config->getBaseUri(Servers::MESSAGINGDEFAULT) . $url,
        );

        $_headers = [
            "user-agent" => BaseController::USER_AGENT,
        ];

        $_httpRequest = $this->makeRequest(HttpMethod::DELETE, $_headers, $url);

        $this->getHttpCallBack()?->callOnBeforeRequest($_httpRequest);

        // and invoke the API call request to fetch the response
        $response = Request::delete($url, $_headers);

        $_httpResponse = new HttpResponse(
            $response->code,
            $response->headers,
            $response->raw_body,
        );
        $_httpContext = new HttpContext($_httpRequest, $_httpResponse);

        $this->getHttpCallBack()?->callOnAfterRequest($_httpContext);

        $this->validateResponse($_httpResponse, $_httpContext);

        return new ApiResponse($response->code, $response->headers, null);
    }

    /**
     * Gets a list of messages based on query parameters.
     *
     * @param string $accountId     User's account ID
     * @param string|null $messageId     (optional) The ID of the message to search for. Special characters need to be
     *                               encoded using URL encoding
     * @param string|null $sourceTn      (optional) The phone number that sent the message
     * @param string|null $destinationTn (optional) The phone number that received the message
     * @param string|null $messageStatus (optional) The status of the message. One of RECEIVED, QUEUED, SENDING, SENT,
     *                               FAILED, DELIVERED, ACCEPTED, UNDELIVERED
     * @param integer|null $errorCode     (optional) The error code of the message
     * @param string|null $fromDateTime  (optional) The start of the date range to search in ISO 8601 format. Uses the
     *                               message receive time. The date range to search in is currently 14 days.
     * @param string|null $toDateTime    (optional) The end of the date range to search in ISO 8601 format. Uses the
     *                               message receive time. The date range to search in is currently 14 days.
     * @param string|null $pageToken     (optional) A base64 encoded value used for pagination of results
     * @param integer|null $limit         (optional) The maximum records requested in search result. Default 100. The sum of
     *                               limit and after cannot be more than 10000
     * @return ApiResponse response from the API call
     * @throws APIException Thrown if API call fails
     */
    public function getMessages(
        string $accountId,
        ?string $messageId = null,
        ?string $sourceTn = null,
        ?string $destinationTn = null,
        ?string $messageStatus = null,
        ?int $errorCode = null,
        ?string $fromDateTime = null,
        ?string $toDateTime = null,
        ?string $pageToken = null,
        ?int $limit = null,
    ) {
        $url = "/users/{$accountId}/messages";

        APIHelper::appendUrlWithQueryParameters($url, [
            "messageId" => $messageId,
            "sourceTn" => $sourceTn,
            "destinationTn" => $destinationTn,
            "messageStatus" => $messageStatus,
            "errorCode" => $errorCode,
            "fromDateTime" => $fromDateTime,
            "toDateTime" => $toDateTime,
            "pageToken" => $pageToken,
            "limit" => $limit,
        ]);

        $url = APIHelper::cleanUrl(
            $this->config->getBaseUri(Servers::MESSAGINGDEFAULT) . $url,
        );

        $_headers = [
            "user-agent" => BaseController::USER_AGENT,
            "Accept" => "application/json",
        ];

        $_httpRequest = $this->makeRequest(HttpMethod::GET, $_headers, $url);

        $this->getHttpCallBack()?->callOnBeforeRequest($_httpRequest);

        // and invoke the API call request to fetch the response
        $response = Request::get($url, $_headers);

        $_httpResponse = new HttpResponse(
            $response->code,
            $response->headers,
            $response->raw_body,
        );
        $_httpContext = new HttpContext($_httpRequest, $_httpResponse);

        $this->getHttpCallBack()?->callOnAfterRequest($_httpContext);

        $this->validateResponse($_httpResponse, $_httpContext);

        $mapper = $this->getJsonMapper();
        $deserializedResponse = $mapper->mapClass(
            $response->body,
            "BandwidthLib\\Messaging\\Models\\BandwidthMessagesList",
        );
        return new ApiResponse(
            $response->code,
            $response->headers,
            $deserializedResponse,
        );
    }

    /**
     * Endpoint for sending text messages and picture messages using V2 messaging.
     *
     * @param string $accountId User's account ID
     * @param MessageRequest $body
     * @return ApiResponse response from the API call
     * @throws APIException Thrown if API call fails
     */
    public function createMessage(string $accountId, MessageRequest $body)
    {
        $url = "/users/{$accountId}/messages";

        $url = APIHelper::cleanUrl(
            $this->config->getBaseUri(Servers::MESSAGINGDEFAULT) . $url,
        );

        $_headers = [
            "user-agent" => BaseController::USER_AGENT,
            "Accept" => "application/json",
            "content-type" => "application/json; charset=utf-8",
        ];

        $_bodyJson = Request\Body::Json($body);

        $_httpRequest = $this->makeRequest(HttpMethod::POST, $_headers, $url);

        $this->getHttpCallBack()?->callOnBeforeRequest($_httpRequest);

        // and invoke the API call request to fetch the response
        $response = Request::post($url, $_headers, $_bodyJson);

        $_httpResponse = new HttpResponse(
            $response->code,
            $response->headers,
            $response->raw_body,
        );
        $_httpContext = new HttpContext($_httpRequest, $_httpResponse);

        $this->getHttpCallBack()?->callOnAfterRequest($_httpContext);

        $this->validateResponse($_httpResponse, $_httpContext);

        $mapper = $this->getJsonMapper();
        $deserializedResponse = $mapper->mapClass(
            $response->body,
            "BandwidthLib\\Messaging\\Models\\BandwidthMessage",
        );
        return new ApiResponse(
            $response->code,
            $response->headers,
            $deserializedResponse,
        );
    }

    public function createMultiChannelMessage(
        string $accountId,
        MultiChannelMessageRequest $body,
    ): void {
        // TODO
    }
}
