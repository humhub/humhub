<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\gates;

/**
 * Classification of an incoming request used to decide whether and how an open
 * user gate responds to it.
 *
 * - `FullPage`: regular browser navigation (GET, accepts `text/html`) — answered with a 302
 * - `Ajax`: XHR / fetch / PJAX / live polling — answered with 401 + JSON `{gate, url}`
 * - `Api`: token-authenticated machine request — answered with 403 + error code, if the
 *   gate applies to API requests at all
 *
 * @see UserGateInterface::appliesTo()
 * @since 1.19
 */
enum RequestClass
{
    case FullPage;
    case Ajax;
    case Api;
}
