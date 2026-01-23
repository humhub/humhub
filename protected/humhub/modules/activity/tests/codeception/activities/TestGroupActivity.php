<?php

namespace humhub\modules\activity\tests\codeception\activities;

use humhub\modules\activity\components\BaseActivity;

class TestGroupActivity extends BaseActivity
{
    public ?int $groupingThreshold = 3;

    public function asText(array $params = []): string
    {
        if ($this->groupCount > 1) {
            return 'Grouped Activity (Total: 5)';
        } else {
            return 'Single Activity';
        }
    }
}
