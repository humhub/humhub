<?php

namespace humhub\modules\content\models\stream;

use humhub\modules\content\models\stream\filters\GlobalContentStreamFilter;
use yii\base\InvalidConfigException;

/**
 * This query class filters global content
 *
 * @package modules\content\models\stream
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
