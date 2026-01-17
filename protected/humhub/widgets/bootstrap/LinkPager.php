<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets\bootstrap;

use humhub\modules\ui\icon\widgets\Icon;

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
        $this->nextPageLabel = (string)Icon::get('step-forward');
        $this->prevPageLabel = (string)Icon::get('step-backward');
        $this->firstPageLabel = (string)Icon::get('fast-backward');
        $this->lastPageLabel = (string)Icon::get('fast-forward');

        parent::init();
    }
}
