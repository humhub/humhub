<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\widgets;

use humhub\helpers\Html;
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

    /**
     * @var bool whether the sections are the groups of an accordion (one open at a time, the first one open) instead
     * of headed blocks below each other
     */
    public bool $accordion = false;

    /**
     * @inheritdoc
     */
    public function run()
    {
        if (!$this->accordion) {
            return parent::run();
        }

        $this->trigger(self::EVENT_RUN);

        $items = '';
        foreach ($this->getWidgets() as $i => [$class, $params]) {
            $section = $this->createSection($class, $params);
            if ($section->isVisible()) {
                $items .= $section->renderAccordionItem($this->getId(), $this->getId() . '-' . $i, $items === '');
            }
        }

        return $items === '' ? '' : Html::tag('div', $items, ['class' => 'accordion', 'id' => $this->getId()]);
    }

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
