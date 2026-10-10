<?php

namespace humhub\modules\activity\tests\codeception\activities;

use humhub\components\message\MessageParam;
use humhub\modules\activity\components\BaseActivity;
use humhub\modules\user\models\User;

/**
 * Adds its parameters through {@see BaseActivity::getMessageParams()}.
 */
class TestMessageParamsActivity extends BaseActivity
{
    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} saw {$params['title']} by {$params['other']} ({$params['note']}, {$params['plain']}, {$params['count']})";
    }

    protected function getMessageParams(): array
    {
        return [
            'title' => MessageParam::emphasis('A <b>&</b> "title"'),
            'other' => MessageParam::user(User::findOne(['id' => 1])),
            'note' => MessageParam::text('x < y', maxLength: 4),
            'plain' => 'a & b',
            'count' => 3,
        ];
    }
}
