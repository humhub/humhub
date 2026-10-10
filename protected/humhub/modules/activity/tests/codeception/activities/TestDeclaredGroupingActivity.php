<?php

namespace humhub\modules\activity\tests\codeception\activities;

use humhub\components\grouping\Grouping;
use humhub\modules\activity\components\BaseActivity;

/**
 * Groups by the {@see $grouping} a test sets.
 */
class TestDeclaredGroupingActivity extends BaseActivity
{
    public static ?Grouping $grouping = null;

    /**
     * Ignored, the threshold of {@see grouping()} applies.
     */
    public int $groupingThreshold = 10;

    public static function grouping(): ?Grouping
    {
        return self::$grouping;
    }

    protected function getMessage(array $params): string
    {
        return "{$params['displayName']} did a thing";
    }
}
