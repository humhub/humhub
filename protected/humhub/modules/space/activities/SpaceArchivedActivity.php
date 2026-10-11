<?php

namespace humhub\modules\space\activities;

use humhub\modules\activity\components\BaseActivity;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use Yii;

class SpaceArchivedActivity extends BaseActivity implements ConfigurableActivityInterface
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

        return Yii::t('SpaceModule.base', '{displayName} has archived the Space {spaceName}.', $params);
    }

    /**
     * @inheritdoc
     */
    protected function getRedactedMessage(array $params): ?string
    {
        return Yii::t('SpaceModule.base', '{displayName} archived a Space.', $params);
    }
}
