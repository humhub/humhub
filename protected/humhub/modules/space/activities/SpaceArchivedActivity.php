<?php

namespace humhub\modules\space\activities;

use humhub\helpers\Html;
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

    public function asText(array $params = []): string
    {
        $defaultParams = [
            'displayName' => $this->user->displayName,
            'spaceName' => $this->space->name,
        ];

        return Yii::t(
            'ActivityModule.base',
            '{displayName} has archived the Space "{spaceName}".',
            array_merge($defaultParams, $params)
        );
    }

    public function asHtml(): string
    {
        return $this->asText([
            'displayName' => Html::strong(Html::encode($this->user->displayName)),
            'spaceName' => Html::strong(Html::encode($this->space->name)),
        ]);
    }
}
