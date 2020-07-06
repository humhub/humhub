<?php


namespace humhub\modules\user\stream;

use humhub\modules\stream\actions\ContentContainerStream;
use humhub\modules\user\models\User;

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
