<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\gates;

use yii\base\Event;

/**
 * Event triggered by the [[GateManager]] to collect user gates from modules.
 *
 * ```php
 * public static function onGateInit(GateInitEvent $event): void
 * {
 *     $event->manager->register(new TwofaGate());
 * }
 * ```
 *
 * @since 1.19
 */
class GateInitEvent extends Event
{
    /**
     * @var GateManager the manager collecting the gates
     */
    public GateManager $manager;
}
