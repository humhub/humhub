<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\widgets;

use humhub\assets\GridViewAsset;

/**
 * @inheritdoc
 */
class GridView extends \yii\grid\GridView
{
    /**
     * @inheritdoc
     */
    public $tableOptions = ['class' => 'table table-hover'];

    /**
     * @inheritdoc
     */
    public function run()
    {
        GridViewAsset::register($this->view);

        return parent::run();
    }
}
