<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets\security;

use humhub\widgets\BaseStack;
use Yii;

/**
 * The sections of one security settings tab
 *
 * `EVENT_INIT` handlers may run several times per request (visibility checks) and must be cheap and side-effect free.
 *
 * @since 1.20
 */
abstract class SecuritySectionStack extends BaseStack
{
    /**
     * @inheritdoc
     */
    public $seperator = '<hr>';

    public function hasVisibleSections(): bool
    {
        foreach ($this->getWidgets() as [$class, $params]) {
            /* @var SecuritySection $section */
            $section = Yii::createObject(array_merge(['class' => $class], $params));
            if ($section->isVisible()) {
                return true;
            }
        }

        return false;
    }
}
