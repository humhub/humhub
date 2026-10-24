<?php

namespace humhub\modules\activity\tests\codeception\activities;

use humhub\modules\activity\components\BaseActivity;

/**
 * Has a sentence without content ({@see BaseActivity::getRedactedMessage()}) and records the
 * parameters it gets.
 */
class TestRedactedActivity extends TestMessageParamsActivity
{
    public static array $redactedParams = [];

    protected function getRedactedMessage(array $params): ?string
    {
        static::$redactedParams = $params;

        return "{$params['displayName']} saw something by {$params['other']}";
    }
}
