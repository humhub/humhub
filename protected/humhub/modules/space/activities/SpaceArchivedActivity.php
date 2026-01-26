<?php

namespace humhub\modules\space\activities;

use humhub\modules\space\components\BaseSpaceActivity;
use Yii;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;

class SpaceArchivedActivity extends BaseSpaceActivity implements ConfigurableActivityInterface
{
    public static function getTitle(): string
    {
        return Yii::t('SpaceModule.activities', 'Space has been archived');
    }

    public static function getDescription(): string
    {
        return Yii::t('SpaceModule.activities', 'Whenever a space is archived.');
    }

    protected function getMessage(array $params): string
    {
        if ($this->inSpaceContext()) {
            return Yii::t('SpaceModule.base', '{displayName} archived this Space.', $params);
        }

        return Yii::t('SpaceModule.base', '{displayName} has the Space {spaceName}.', $params);
    }
}
