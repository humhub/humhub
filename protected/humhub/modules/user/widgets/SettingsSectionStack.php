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
     * @var bool whether the sections render as an accordion with one open item: the first one, or the one whose
     * form was submitted with errors
     */
    public bool $accordion = false;

    public function hasVisibleSections(): bool
    {
        foreach ($this->getWidgets() as [$class, $params]) {
            if ($this->createSection($class, $params)->isVisible()) {
                return true;
            }
        }

        return false;
    }

    public function run()
    {
        if (!$this->accordion) {
            return parent::run();
        }

        $this->trigger(self::EVENT_RUN);

        $items = [];
        foreach ($this->getWidgets() as [$class, $params]) {
            $section = $this->createSection($class, $params);
            if ($section->isVisible()) {
                $items[] = [$class, $params, $section->hasErrors()];
            }
        }
        if ($items === []) {
            return '';
        }

        $open = array_search(true, array_column($items, 2), true);
        $open = $open === false ? 0 : $open;

        $content = '';
        foreach ($items as $index => [$class, $params]) {
            $content .= $class::widget(array_merge($params, [
                'accordionId' => $this->getId(),
                'expanded' => $index === $open,
            ]));
        }

        return Html::tag('div', $content, ['class' => 'accordion', 'id' => $this->getId()]);
    }

    private function createSection(string $class, array $params): SettingsSection
    {
        return Yii::createObject(array_merge(['class' => $class], $params));
    }
}
