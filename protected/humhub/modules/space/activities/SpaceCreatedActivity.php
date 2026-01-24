<?php

namespace humhub\modules\space\activities;

use humhub\helpers\Html;
use humhub\modules\space\components\BaseSpaceActivity;
use Yii;

class SpaceCreatedActivity extends BaseSpaceActivity
{
    public function asText(array $params = []): string
    {
        $defaultParams = [
            'displayName' => $this->user->displayName,
            'spaceName' => $this->space->name,
        ];

        return Yii::t(
            'ActivityModule.base',
            '{displayName} created the new space {spaceName}.',
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
