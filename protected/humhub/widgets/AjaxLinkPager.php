<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use yii\bootstrap5\Html;
use yii\helpers\ArrayHelper;

/**
 * AjaxLinkPager
 *
 * @inheritdoc
 * @author luke
 */
class AjaxLinkPager extends \humhub\widgets\bootstrap\LinkPager
{
    /**
     * @inerhitdoc
     */
    protected function renderPageButton(string $label, int $page, string $class, bool $disabled, bool $active): string
    {
        // Same code as parent:
        $options = $this->linkContainerOptions;
        $linkWrapTag = ArrayHelper::remove($options, 'tag', 'li');
        Html::addCssClass($options, $class ?: $this->pageCssClass);

        $linkOptions = $this->linkOptions;
        $linkOptions['data']['page'] = $page;

        if ($active) {
            $options['aria'] = ['current' => 'page'];
            Html::addCssClass($options, $this->activePageCssClass);
        }
        if ($disabled) {
            Html::addCssClass($options, $this->disabledPageCssClass);
            $disabledItemOptions = $this->disabledListItemSubTagOptions;
            $linkOptions = ArrayHelper::merge($linkOptions, $disabledItemOptions);
            $linkOptions['tabindex'] = '-1';
        }

        // Modifications for Ajax:
        $linkOptions['data']['action-click'] = 'ui.modal.post';
        $linkOptions['data']['action-url'] = $this->pagination->createUrl($page);
        return Html::tag($linkWrapTag, Html::a($label, '#', $linkOptions), $options);
    }
}
