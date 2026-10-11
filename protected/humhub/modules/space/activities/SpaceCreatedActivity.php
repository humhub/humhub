<?php

namespace humhub\modules\space\activities;

use humhub\modules\activity\components\BaseActivity;
use Yii;

class SpaceCreatedActivity extends BaseActivity
{
    protected function getMessage(array $params): string
    {
        if ($this->inSpaceContext()) {
            return Yii::t('SpaceModule.base', '{displayName} created this Space.', $params);
        }

        return Yii::t('SpaceModule.base', '{displayName} created the new Space {spaceName}.', $params);
    }

    /**
     * @inheritdoc
     */
    protected function getRedactedMessage(array $params): ?string
    {
        return Yii::t('SpaceModule.base', '{displayName} created a new Space.', $params);
    }
}
