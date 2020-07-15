<?php


namespace humhub\modules\user\actions;

use humhub\modules\stream\actions\ContentContainerStream;
use humhub\modules\user\models\User;
use humhub\modules\user\stream\ProfileStreamQuery;

/**
 * ProfileStream
 *
 * @package humhub\modules\user\components
 */
class ProfileStreamAction extends ContentContainerStream
{
    /**
     * @inheritdoc
     */
    public $streamQueryClass = ProfileStreamQuery::class;

    /**
     * @inheritdoc
     */
    protected function beforeRun()
    {
        if(!$this->contentContainer instanceof User) {
            return false;
        }

        return parent::beforeRun();
    }
}
