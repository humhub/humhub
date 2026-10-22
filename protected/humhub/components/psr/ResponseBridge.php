<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\psr;

use Psr\Http\Message\ResponseInterface;
use yii\web\Response;

/**
 * Copies a PSR-7 response into the Yii response.
 *
 * @since 1.20
 */
final class ResponseBridge
{
    public static function toYii(ResponseInterface $psr, Response $response): Response
    {
        $response->format = Response::FORMAT_RAW;
        $response->setStatusCode($psr->getStatusCode());

        foreach ($psr->getHeaders() as $name => $values) {
            $response->getHeaders()->remove($name);
            foreach ($values as $value) {
                $response->getHeaders()->add($name, $value);
            }
        }

        $response->content = (string)$psr->getBody();

        return $response;
    }
}
