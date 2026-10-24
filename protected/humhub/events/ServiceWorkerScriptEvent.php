<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\events;

use yii\base\Event;

/**
 * ServiceWorkerScriptEvent carries the JavaScript served as `/sw.js` while it is being assembled.
 *
 * Modules extend the service worker by appending their own script in a
 * {@see \humhub\services\ServiceWorkerService::EVENT_BUILD_SCRIPT} handler.
 *
 * @since 1.20
 */
class ServiceWorkerScriptEvent extends Event
{
    /**
     * @var string the service worker script assembled so far
     */
    public string $script = '';

    /**
     * Appends JavaScript to the service worker script.
     */
    public function append(string $js): void
    {
        $this->script .= $js;
    }
}
