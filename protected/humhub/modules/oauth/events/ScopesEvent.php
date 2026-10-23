<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\oauth\events;

use humhub\modules\oauth\models\Scope;
use yii\base\Event;

/**
 * Collects the scopes modules provide, see `ScopeRegistry::EVENT_COLLECT_SCOPES`.
 *
 * @since 1.20
 */
class ScopesEvent extends Event
{
    /**
     * @var Scope[]
     */
    public array $scopes = [];
}
