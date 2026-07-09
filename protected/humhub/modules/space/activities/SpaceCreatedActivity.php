<?php

namespace humhub\modules\space\activities;

use humhub\modules\space\components\BaseSpaceActivity;
use Yii;

class SpaceCreatedActivity extends BaseSpaceActivity
{
    protected function getMessage(array $params): string
    {
        if ($this->inSpaceContext()) {
            return Yii::t('SpaceModule.base', '{displayName} created this Space.', $params);
        }

        return Yii::t('SpaceModule.base', '{displayName} created the new Space {spaceName}.', $params);
    }
}
