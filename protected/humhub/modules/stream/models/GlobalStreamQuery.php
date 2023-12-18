<?php

namespace humhub\modules\stream\models;

use humhub\modules\stream\models\filters\GlobalStreamFilter;
use yii\base\InvalidConfigException;

/**
 * This query class filters global content
 *
 * @package modules\stream\models
 * @since 1.16
 */
class GlobalStreamQuery extends WallStreamQuery
{
    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    protected function beforeApplyFilters()
    {
        $this->addFilterHandler(
            new GlobalStreamFilter(),
            true,
            true
        );

        parent::beforeApplyFilters();
    }
}
