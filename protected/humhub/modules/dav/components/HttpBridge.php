<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\components;

use Sabre\HTTP\Request as SabreRequest;
use Sabre\HTTP\ResponseInterface;
use yii\web\Request;
use yii\web\Response;

/**
 * Turns Yii's request into Sabre's and Sabre's response into Yii's.
 *
 * @since 1.20
 */
final class HttpBridge
{
    /**
     * The response headers of every request: a file is served from the platform's origin, so
     * a browser must neither run nor sniff it — an uploaded HTML file would otherwise run its
     * scripts there.
     */
    public const SECURITY_HEADERS = [
        'X-Content-Type-Options' => 'nosniff',
        'Content-Security-Policy' => "default-src 'none'; sandbox",
    ];

    /**
     * The request as Sabre reads it, its body the raw input stream.
     *
     * The method is the one of the request line: Yii's `getMethod()` also takes it from a
     * `_method` parameter and the `X-Http-Method-Override` header, so a form of another site
     * could turn a `POST` into a `DELETE`.
     *
     * @param resource|string|null $body the body, `php://input` when null
     */
    public static function request(Request $request, ?string $method = null, mixed $body = null): SabreRequest
    {
        $method ??= strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));

        $sabre = new SabreRequest($method, $request->getUrl(), $request->getHeaders()->toArray(), $body ?? fopen('php://input', 'rb'));
        $sabre->setAbsoluteUrl($request->getHostInfo() . $request->getUrl());

        if (preg_match('#^HTTP/(\d(?:\.\d)?)$#', (string)($_SERVER['SERVER_PROTOCOL'] ?? ''), $matches)) {
            $sabre->setHttpVersion($matches[1]);
        }

        return $sabre;
    }

    /**
     * Copies status, headers and body of Sabre's response into Yii's. A body that is a stream
     * is handed over as a stream (only its `Content-Length` bytes, as Sabre would send them),
     * so a file is never read into memory.
     *
     * A server error says no more than its status: Sabre would put the exception's message
     * into the body, whatever it was thrown with.
     */
    public static function respond(ResponseInterface $sabre, Response $response, bool $isHead = false): Response
    {
        $response->format = Response::FORMAT_RAW;
        $response->setStatusCode($sabre->getStatus(), $sabre->getStatusText() ?: null);

        foreach ($sabre->getHeaders() as $name => $values) {
            $response->headers->remove($name);
            foreach ((array)$values as $value) {
                $response->headers->add($name, (string)$value);
            }
        }
        foreach (self::SECURITY_HEADERS as $name => $value) {
            $response->headers->set($name, $value);
        }

        $body = $sabre->getBody();

        if ($sabre->getStatus() >= 500) {
            self::close($body);
            $response->headers->set('Content-Type', 'text/plain; charset=utf-8');
            $response->headers->remove('Content-Length');
            $response->content = $isHead ? '' : $sabre->getStatusText();
            return $response;
        }

        if ($isHead || $body === null) {
            self::close($body);
            $response->content = '';
            return $response;
        }

        if (is_resource($body)) {
            $length = $sabre->getHeader('Content-Length');
            if ($length === null || !is_numeric($length)) {
                $response->stream = $body;
            } else {
                $start = (int)ftell($body);
                $response->stream = [$body, $start, $start + (int)$length - 1];
            }
            return $response;
        }

        if (is_callable($body)) {
            ob_start();
            $body();
            $response->content = (string)ob_get_clean();
            return $response;
        }

        $response->content = (string)$body;
        return $response;
    }

    private static function close(mixed $body): void
    {
        if (is_resource($body)) {
            fclose($body);
        }
    }
}
