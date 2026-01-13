<?php

namespace humhub\modules\space\activities;

use humhub\helpers\Html;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use humhub\modules\space\components\BaseSpaceActivity;
use Yii;

class MemberRemovedActivity extends BaseSpaceActivity implements ConfigurableActivityInterface
{
    public static function getTitle(): string
    {
        return Yii::t('SpaceModule.activities', 'Space member left');
    }

    public static function getDescription(): string
    {
        return Yii::t('SpaceModule.activities', 'Whenever a member leaves one of your spaces.');
    }

    public function getAsText(array $params = []): string
    {
        $defaultParams = [
            'displayName' => $this->user->displayName,
            'spaceName' => $this->space->name,
        ];

        return Yii::t(
            'ActivityModule.base',
            '{displayName} left the Space {spaceName}.',
            array_merge($defaultParams, $params)
        );
    }

    public function getAsHtml()
    {
        return $this->getAsText([
            'displayName' => Html::strong(Html::encode($this->user->displayName)),
            'spaceName' => Html::strong(Html::encode($this->space->name)),
        ]);
    }
}
