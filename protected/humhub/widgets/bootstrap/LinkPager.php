<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\bootstrap;

use humhub\helpers\Html;
use humhub\widgets\Icon;
use Yii;

/**
 * @inheritdoc
 */
class LinkPager extends \yii\bootstrap5\LinkPager
{
    /**
     * @inheritdoc
     */
    public $maxButtonCount = 5;

    public function init(): void
    {
        // Icon-only links, named for screen readers (WCAG 2.4.4, 4.1.2)
        $this->nextPageLabel = static::iconLabel('step-forward', Yii::t('base', 'Next page'));
        $this->prevPageLabel = static::iconLabel('step-backward', Yii::t('base', 'Previous page'));
        $this->firstPageLabel = static::iconLabel('fast-backward', Yii::t('base', 'First page'));
        $this->lastPageLabel = static::iconLabel('fast-forward', Yii::t('base', 'Last page'));

        parent::init();
    }

    private static function iconLabel(string $icon, string $text): string
    {
        return Icon::get($icon) . Html::tag('span', $text, ['class' => 'visually-hidden']);
    }
}
