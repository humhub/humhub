<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\libs\Html;
use humhub\widgets\assets\AjaxLinkPagerAsset;
use yii\helpers\ArrayHelper;

/**
 * AjaxLinkPager
 *
 * @inheritdoc
 * @author luke
 */
class AjaxLinkPager extends \humhub\widgets\LinkPager
{
    protected function renderPageButton($label, $page, $class, $disabled, $active)
    {
        $options = ['class' => $class === '' ? null : $class];
        if ($active) {
            Html::addCssClass($options, $this->activePageCssClass);
        }
        if ($disabled) {
            Html::addCssClass($options, $this->disabledPageCssClass);

            return Html::tag('li', Html::tag('span', $label), $options);
        }

        AjaxLinkPagerAsset::register($this->view);

        return Html::tag(
            'li',
            Html::a($label, '#', ArrayHelper::merge([
                'data' => [
                    'page' => $page,
                    'action-click' => 'ajaxLinkPager.setPage',
                    'action-url' => $this->pagination->createUrl($page),
                ],
            ], $this->linkOptions)),
            $options,
        );
    }
}
