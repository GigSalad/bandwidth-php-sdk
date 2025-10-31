<?php
namespace BandwidthLib\Messaging\Controllers;

use BandwidthLib\APIException;
use BandwidthLib\APIHelper;
use BandwidthLib\Messaging\Exceptions\MessagingException;
use BandwidthLib\Messaging\Models\BandwidthMessage;
use BandwidthLib\Messaging\Models\BandwidthMessagesList;
use BandwidthLib\Messaging\Models\BandwidthMultiChannelMessage;
use BandwidthLib\Messaging\Models\Media;
use BandwidthLib\Messaging\Models\MessageRequest;
use BandwidthLib\Messaging\Models\MultiChannelMessageRequest;
use BandwidthLib\Controllers\BaseController;
use BandwidthLib\Http\ApiResponse;
use BandwidthLib\Http\HttpRequest;
use BandwidthLib\Http\HttpResponse;
use BandwidthLib\Http\HttpContext;
use BandwidthLib\Servers;
use Unirest\Request;
use Unirest\Request\Body;
use Unirest\Response;
use Unirest\Method;

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
        $this->throwWhenSpecificResponses($response->getStatusCode(), $context);

        parent::validateResponse($response, $context);
    }

    /**
     * Throw specific exceptions for certain HTTP response codes.
     *
     * @throws MessagingException when response code is a certain value
     */
    protected function throwWhenSpecificResponses(
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
     * Creates custom request object, performs actual request via
     * Unirest\Request, then creates custom response object. The custom
     * objects combine into a custom context object, which is used for
     * callback and validation. Lastly, Unirest\Response is returned.
     *
     * @param mixed[] $headers
     */
    protected function makeRequest(
        string $method,
        string $url,
        array $headers = [],
        ?string $body = null,
    ): Response {
        Request::auth(
            $this->config->getMessagingBasicAuthUserName(),
            $this->config->getMessagingBasicAuthPassword(),
        );

        Request::timeout($this->config->getTimeout());

        $url = APIHelper::cleanUrl(
            $this->config->getBaseUri(Servers::MESSAGINGDEFAULT) . $url,
        );

        $headers = [...$headers, "user-agent" => BaseController::USER_AGENT];

        $httpRequest = new HttpRequest($method, $headers, $url);

        $this->getHttpCallBack()?->callOnBeforeRequest($httpRequest);

        $response = Request::send($method, $url, $body, $headers);

        $httpResponse = new HttpResponse(
            $response->code,
            $response->headers,
            $response->raw_body,
        );

        $httpContext = new HttpContext($httpRequest, $httpResponse);

        $this->getHttpCallBack()?->callOnAfterRequest($httpContext);

        $this->validateResponse($httpResponse, $httpContext);

        return $response;
    }

    protected function buildApiResponse(
        Response $response,
        ?string $mapToClass = null,
        bool $nullResponseBody = false,
    ): ApiResponse {
        $responseBody = $response->body;

        if ($mapToClass) {
            $responseBody = \is_array($responseBody)
                ? $this->getJsonMapper()->mapClassArray(
                    $responseBody,
                    $mapToClass,
                )
                : $this->getJsonMapper()->mapClass($responseBody, $mapToClass);
        }

        return new ApiResponse(
            $response->code,
            $response->headers,
            $nullResponseBody ? null : $responseBody,
        );
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
    ): ApiResponse {
        $url = "/users/{$accountId}/media";

        $headers = [
            "Accept" => "application/json",
            "Continuation-Token" => $continuationToken,
        ];

        $response = $this->makeRequest(Method::GET, $url, $headers);

        return $this->buildApiResponse($response, Media::class);
    }

    /**
     * Downloads a media file you previously uploaded.
     *
     * @param string $accountId User's account ID
     * @param string $mediaId   Media ID to retrieve
     * @return ApiResponse response from the API call
     * @throws APIException Thrown if API call fails
     */
    public function getMedia(string $accountId, string $mediaId): ApiResponse
    {
        $url = "/users/{$accountId}/media/{$mediaId}";

        $response = $this->makeRequest(Method::GET, $url);

        return $this->buildApiResponse($response);
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
        ?string $contentType = "application/octet-stream",
        ?string $cacheControl = null,
    ): ApiResponse {
        $url = "/users/{$accountId}/media/{$mediaId}";

        $headers = [
            "Content-Type" => $contentType ?: "application/octet-stream",
            "Cache-Control" => $cacheControl,
        ];

        $response = $this->makeRequest(Method::PUT, $url, $headers, $body);

        return $this->buildApiResponse($response, nullResponseBody: true);
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
    public function deleteMedia(string $accountId, string $mediaId): ApiResponse
    {
        $url = "/users/{$accountId}/media/{$mediaId}";

        $response = $this->makeRequest(Method::DELETE, $url);

        return $this->buildApiResponse($response, nullResponseBody: true);
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
    ): ApiResponse {
        $url = "/users/{$accountId}/messages";

        $queryString = http_build_query([
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

        if ($queryString) {
            $url .= "?{$queryString}";
        }

        $headers = [
            "Accept" => "application/json",
        ];

        $response = $this->makeRequest(Method::GET, $url, $headers);

        return $this->buildApiResponse($response, BandwidthMessagesList::class);
    }

    /**
     * Send SMS text messages or multi-channel messages.
     *
     * @throws APIException Thrown if API call fails
     */
    public function createMessage(
        string $accountId,
        MessageRequest|MultiChannelMessageRequest $body,
    ): ApiResponse {
        [$url, $mapResponseToClass] = match ($body::class) {
            MessageRequest::class => [
                "/users/{$accountId}/messages",
                BandwidthMessage::class,
            ],
            MultiChannelMessageRequest::class => [
                "/users/{$accountId}/messages/multiChannel",
                BandwidthMultiChannelMessage::class,
            ],
        };

        $headers = [
            "Accept" => "application/json",
            "content-type" => "application/json; charset=utf-8",
        ];

        $body = Body::Json($body) ?: "";

        $response = $this->makeRequest(Method::POST, $url, $headers, $body);

        return $this->buildApiResponse($response, $mapResponseToClass);
    }

    public function createMultiChannelMessage(
        string $accountId,
        MultiChannelMessageRequest $body,
    ): ApiResponse {
        return $this->createMessage($accountId, $body);
    }
}
