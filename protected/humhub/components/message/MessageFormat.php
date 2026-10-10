<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\message;

/**
 * The markup a message is rendered in, see {@see MessageParam::render()}.
 *
 * @since 1.20
 */
enum MessageFormat
{
    /**
     * HTML, e.g. the web list or an HTML mail: values are HTML-encoded.
     */
    case Html;

    /**
     * Plain text, e.g. a text mail, a mail subject or a push message: values are inserted as they are.
     */
    case Text;
}
