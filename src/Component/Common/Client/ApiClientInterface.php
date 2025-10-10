<?php

namespace Misery\Component\Common\Client;

use Misery\Component\Common\Generator\UrlGenerator;

/**
HTTP Status Codes Legend:

2xx Success:
- 200 OK: Standard success response.
- 201 Created: Resource successfully created.
- 202 Accepted: Request accepted but not completed.
- 203 Non-Authoritative Information: Modified response from a proxy.
- 204 No Content: Success, no "body" returned.
- 205 Reset Content: Reset the document view.
- 206 Partial Content: Partial response (e.g., range request).
- 207 Multi-Status: WebDAV; multiple status codes for batch operations.
- 208 Already Reported: WebDAV; avoids duplicate reports of resources.
- 226 IM Used: Response includes delta encoding.

3xx Redirection (usually not an error but may require handling):
- 300 Multiple Choices
- 301 Moved Permanently
- 302 Found
- 303 See Other
- 304 Not Modified
- 305 Use Proxy (deprecated)
- 307 Temporary Redirect
- 308 Permanent Redirect

4xx Client Errors (usually throw exception):
- 400 Bad Request
- 401 Unauthorized
- 402 Payment Required (reserved)
- 403 Forbidden
- 404 Not Found
- 405 Method Not Allowed
- 406 Not Acceptable
- 407 Proxy Authentication Required
- 408 Request Timeout
- 409 Conflict
- 410 Gone
- 411 Length Required
- 412 Precondition Failed
- 413 Payload Too Large
- 414 URI Too Long
- 415 Unsupported Media Type
- 416 Range Not Satisfiable
- 417 Expectation Failed
- 418 I'm a teapot (Easter egg)
- 422 Unprocessable Entity (WebDAV)
- 423 Locked (WebDAV)
- 424 Failed Dependency (WebDAV)
- 425 Too Early
- 426 Upgrade Required
- 428 Precondition Required
- 429 Too Many Requests
- 431 Request Header Fields Too Large
- 451 Unavailable For Legal Reasons

5xx Server Errors (usually throw exception):
- 500 Internal Server Error
- 501 Not Implemented
- 502 Bad Gateway
- 503 Service Unavailable
- 504 Gateway Timeout
- 505 HTTP Version Not Supported
- 506 Variant Also Negotiates
- 507 Insufficient Storage (WebDAV)
- 508 Loop Detected (WebDAV)
- 510 Not Extended
- 511 Network Authentication Required
**/
interface ApiClientInterface
{
    /**
     * A GET HTTP VERB
     */
    public function get(string $endpoint): ApiResponse;
    /**
     * A POST HTTP VERB
     * $postData is a structured entity array that will be encoded to json
     */
    public function post(string $endpoint, array $postData, array $headers = []): ApiResponse;
    /**
     * HTTP PATCH VERB That supports a multi patch insert
     * max 100 inserts per request
     */
    public function multiPatch(string $endpoint, array $dataSet): ApiResponse;
    /**
     * HTTP PATCH VERB
     */
    public function patch(string $endpoint, array $patchData): ApiResponse;
    /**
     * A DELETE HTTP VERB
     */
    public function delete(string $endpoint): ApiResponse;

    public function log(string $message, int $statusCode = null, $content): void;

    public function getUrlGenerator(): UrlGenerator;

    public function getPaginator(string $startUrl): PaginationCursor;

    public function getApiEndpoint(string $apiEndpoint): ApiEndpointInterface;
}
