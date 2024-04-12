<?php

namespace humhub\modules\stream\models;

use humhub\modules\stream\models\filters\GlobalContentStreamFilter;
use yii\base\InvalidConfigException;

/**
 * This query class filters global content
 *
 * @package modules\stream\models
 * @since 1.16
 */
class GlobalContentStreamQuery extends WallStreamQuery
{
    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    protected function beforeApplyFilters(): void
    {
        $this->addFilterHandler(
            new GlobalContentStreamFilter(),
            true,
            true,
        );

        parent::beforeApplyFilters();
    }
}
