<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dav\components;

use Sabre\HTTP\Request;
use Sabre\HTTP\ResponseInterface;
use Sabre\HTTP\Sapi;

/**
 * Keeps Sabre away from PHP's globals: the request comes from Yii's ([[HttpBridge::request()]]),
 * the response goes into Yii's ([[HttpBridge::respond()]]), which sends it like any other.
 *
 * @since 1.20
 */
class NullSapi extends Sapi
{
    /**
     * @inheritdoc
     *
     * A placeholder: the server gets its request set right after it is built.
     */
    public static function getRequest(): Request
    {
        return new Request('GET', '/');
    }

    /**
     * @inheritdoc
     */
    public static function sendResponse(ResponseInterface $response)
    {
    }
}
