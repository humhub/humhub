<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\widgets\BaseStack;
use Yii;

/**
 * The sections of one tab of the account settings
 *
 * Modules add sections on `EVENT_INIT` with `addWidget()`. The handlers may run several times per request
 * (visibility checks) and must be cheap and side-effect free.
 *
 * @since 1.20
 */
abstract class SettingsSectionStack extends BaseStack
{
    /**
     * @inheritdoc
     */
    public $seperator = '<hr>';

    public function hasVisibleSections(): bool
    {
        foreach ($this->getWidgets() as [$class, $params]) {
            if ($this->createSection($class, $params)->isVisible()) {
                return true;
            }
        }

        return false;
    }

    private function createSection(string $class, array $params): SettingsSection
    {
        return Yii::createObject(array_merge(['class' => $class], $params));
    }
}
