<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\psr;

use GuzzleHttp\Psr7\ServerRequest;
use Psr\Http\Message\ServerRequestInterface;
use yii\web\Request;

/**
 * Builds a PSR-7 server request from the Yii request.
 *
 * @since 1.20
 */
final class ServerRequestFactory
{
    public static function fromYii(Request $request): ServerRequestInterface
    {
        $factory = HttpFactory::instance();

        $psr = $factory->createServerRequest($request->getMethod(), $request->getAbsoluteUrl(), $_SERVER);

        foreach ($request->getHeaders() as $name => $values) {
            $psr = $psr->withHeader($name, $values);
        }

        $psr = $psr
            ->withBody($factory->createStream($request->getRawBody()))
            ->withQueryParams($request->getQueryParams())
            ->withCookieParams($_COOKIE)
            ->withUploadedFiles(ServerRequest::normalizeFiles($_FILES));

        $bodyParams = $request->getBodyParams();
        if (is_array($bodyParams) && $bodyParams !== []) {
            $psr = $psr->withParsedBody($bodyParams);
        }

        return $psr;
    }
}
