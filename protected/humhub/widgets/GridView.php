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
    protected function initColumns()
    {
        parent::initColumns();

        // A header cell heads its column (WCAG 1.3.1), unless a column says otherwise
        foreach ($this->columns as $column) {
            $column->headerOptions['scope'] ??= 'col';
        }
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        GridViewAsset::register($this->view);

        return parent::run();
    }
}
