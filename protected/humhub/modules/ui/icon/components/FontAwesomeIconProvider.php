<?php

namespace humhub\modules\ui\icon\components;

use humhub\helpers\Html;
use humhub\modules\ui\icon\widgets\Icon;

/**
 * Class FontAwesomeIconFactory
 * @package humhub\modules\ui\icon
 *
 * @since 1.4
 */
class FontAwesomeIconProvider implements IconProvider
{
    /**
     * @return string
     */
    public function getId()
    {
        return 'fa';
    }

    /**
     * @param Icon $icon
     * @return string
     */
    public function render($icon, $options = [])
    {
        $icon = Icon::get($icon, $options);

        $options = $icon->htmlOptions;

        Html::addCssClass($options, 'fa fa-' . $icon->name);

        if ($icon->size) {
            Html::addCssClass($options, $this->getIconSizeClass($icon));
        }

        if ($icon->fixedWidth) {
            Html::addCssClass($options, 'fa-fw');
        }

        if ($icon->listItem) {
            Html::addCssClass($options, 'fa-li');
        }

        if ($icon->right) {
            Html::addCssClass($options, 'fa-pull-right');
        }

        if ($icon->left) {
            Html::addCssClass($options, 'fa-pull-left');
        }

        if ($icon->border) {
            Html::addCssClass($options, 'fa-border');
        }

        $options['aria-hidden'] = 'true';

        $ariaElement = '';

        if ($icon->tooltip) {
            $options['role'] = 'img';
            Html::addTooltip($options, $icon->tooltip);
            $ariaLabel = $icon->ariaLabel ?? $icon->tooltip;
            $ariaElement = Html::tag('span', $ariaLabel, ['class' => 'sr-only']);
        }

        if ($icon->color) {
            Html::addCssStyle($options, ['color' => $icon->color]);
        }


        return Html::beginTag('i', $options) . Html::endTag('i') . $ariaElement;
    }

    /**
     * @param $listDefinition []
     * @return $
     */
    public function renderList($listDefinition)
    {
        $items = [];
        foreach ($listDefinition as $listItem) {
            $text = reset($listItem);
            $iconName = key($listItem);
            $options = $listItem['options'] ?? [];
            $options['listItem'] = true;
            $items[] = $this->render($iconName, $options) . $text;
        }

        return Html::ul($items, ['class' => 'fa-ul', 'encode' => false]);
    }

    private function getIconSizeClass(Icon $icon)
    {
        return match ($icon->size) {
            Icon::SIZE_SM => 'fa-sm',
            Icon::SIZE_LG => 'fa-lg',
            Icon::SIZE_2x => 'fa-2x',
            Icon::SIZE_3x => 'fa-3x',
            Icon::SIZE_4x => 'fa-4x',
            Icon::SIZE_5x => 'fa-5x',
            Icon::SIZE_6x => 'fa-6x',
            Icon::SIZE_7x => 'fa-7x',
            Icon::SIZE_8x => 'fa-8x',
            Icon::SIZE_9x => 'fa-9x',
            Icon::SIZE_10x => 'fa-10x',
            default => null,
        };
    }

    /**
     * @inheritdoc
     */
    public function getNames()
    {
        return Icon::$names;
    }
}
