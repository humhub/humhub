<?php
namespace humhub\modules\ui\icon\components;

use humhub\libs\Html;
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
        $icon = Icon::get($icon,$options);

        $options = $icon->htmlOptions;

        Html::addCssClass($options, 'fa fa-'.$icon->name);

        if($icon->size) {
            Html::addCssClass($options, $this->getIconSizeClass($icon));
        }

        if($icon->fixedWidth) {
            Html::addCssClass($options, 'fa-fw');
        }

        if($icon->listItem) {
            Html::addCssClass($options, 'fa-li');
        }

        if($icon->right) {
            Html::addCssClass($options, 'fa-pull-right');
        }

        if($icon->left) {
            Html::addCssClass($options, 'fa-pull-left');
        }

        if($icon->border) {
            Html::addCssClass($options, 'fa-border');
        }

        $options['aria-hidden'] = 'true';

        $ariaElement = '';

        if($icon->tooltip) {
            $options['role'] = 'img';
            Html::addTooltip($options, $icon->tooltip);
            $ariaLabel = $icon->ariaLabel ?? $icon->tooltip;
            $ariaElement = Html::tag('span', $ariaLabel, ['class' => 'sr-only']);
        }

        if($icon->color) {
            Html::addCssStyle($options, ['color' => $icon->color]);
        }



        return Html::beginTag('i', $options).Html::endTag('i') . $ariaElement;
    }

    /**
     * @param $listDefinition []
     * @return $
     */
    public function renderList($listDefinition)
    {
        $items = [];
        foreach ($listDefinition as $listItem)
        {
            $text = reset($listItem);
            $iconName = key($listItem);
            $options = (isset($listItem['options'])) ? $listItem['options'] : [];
            $options['listItem'] = true;
            $items[] = $this->render($iconName, $options).$text;
        }

        return Html::ul($items, ['class' => 'fa-ul', 'encode' => false]);
    }

    private function getIconSizeClass(Icon $icon)
    {
        switch ($icon->size) {
            case Icon::SIZE_SM:
                return 'fa-sm';
            case Icon::SIZE_LG:
                return 'fa-lg';
            case Icon::SIZE_2x:
                return 'fa-2x';
            case Icon::SIZE_3x:
                return 'fa-3x';
            case Icon::SIZE_4x:
                return 'fa-4x';
            case Icon::SIZE_5x:
                return 'fa-5x';
            case Icon::SIZE_6x:
                return 'fa-6x';
            case Icon::SIZE_7x:
                return 'fa-7x';
            case Icon::SIZE_8x:
                return 'fa-8x';
            case Icon::SIZE_9x:
                return 'fa-9x';
            case Icon::SIZE_10x:
                return 'fa-10x';
            default:
                return null;
        }
    }

    /**
     * @inheritdoc
     */
    public function getNames()
    {
        return Icon::$names;
    }
}
