<?php

namespace humhub\modules\space\activities;

use humhub\helpers\Html;
use humhub\modules\activity\interfaces\ConfigurableActivityInterface;
use humhub\modules\space\components\BaseSpaceActivity;
use Yii;

class MemberAddedActivity extends BaseSpaceActivity implements ConfigurableActivityInterface
{
    public static function getTitle(): string
    {
        return Yii::t('SpaceModule.activities', 'Space member joined');
    }

    public static function getDescription(): string
    {
        return Yii::t('SpaceModule.activities', 'Whenever a new member joined one of your spaces.');
    }

    public function getAsText(array $params = []): string
    {
        $defaultParams = [
            'displayName' => $this->user->displayName,
            'spaceName' => $this->space->name,
        ];

        if ($this->inSpaceContext()) {
            return Yii::t(
                'ActivityModule.base',
                '{displayName} joined this Space.',
                array_merge($defaultParams, $params),
            );
        }

        return Yii::t(
            'ActivityModule.base',
            '{displayName} joined the Space {spaceName}.',
            array_merge($defaultParams, $params),
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
