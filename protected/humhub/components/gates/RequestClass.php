<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\gates;

/**
 * Classification of an incoming request used to decide whether a user gate applies to it.
 * It reflects the server-side authentication context, not client-supplied content
 * negotiation, so a gate cannot be escaped by spoofing request headers
 * (see [[GateFilter::getRequestClass()]]).
 *
 * - `FullPage`: session-authenticated request that is not an XHR (typically a browser
 *   navigation)
 * - `Ajax`: session-authenticated XHR / fetch / PJAX / live polling request
 * - `Api`: token-/stateless-authenticated request (e.g. REST, CalDAV — the session is
 *   disabled server-side)
 *
 * How an intercepted request is answered (302 / 401 / 403) is chosen separately, based on
 * content negotiation; see [[GateFilter]].
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
