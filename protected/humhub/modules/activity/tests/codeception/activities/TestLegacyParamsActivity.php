<?php

namespace humhub\modules\activity\tests\codeception\activities;

use humhub\helpers\Html;
use humhub\modules\activity\components\BaseActivity;

/**
 * Adds its parameters by overriding the per-output methods deprecated in 1.20, as a 1.19 module does.
 */
class TestLegacyParamsActivity extends BaseActivity
{
    protected function getMessage(array $params): string
    {
        return "{$params['displayName']}|{$params['legacy']}|{$params['textOnly']}";
    }

    protected function getMessageParamsMailText(): array
    {
        return array_merge(parent::getMessageParamsMailText(), [
            'legacy' => 'text',
            'textOnly' => 'from text',
        ]);
    }

    protected function getMessageParamsWeb(): array
    {
        return array_merge(parent::getMessageParamsWeb(), [
            'displayName' => Html::tag('em', Html::encode($this->user->displayName)),
            'legacy' => '<i>web</i>',
        ]);
    }
}
