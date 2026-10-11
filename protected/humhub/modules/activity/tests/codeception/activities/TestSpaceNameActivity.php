<?php

namespace humhub\modules\activity\tests\codeception\activities;

use humhub\modules\activity\components\BaseContentActivity;

/**
 * A content activity naming the space through the built-in `spaceName`.
 */
class TestSpaceNameActivity extends BaseContentActivity
{
    protected function getMessage(array $params): string
    {
        return $params['displayName'] . ' wrote in ' . ($params['spaceName'] ?? '(no space)');
    }
}
