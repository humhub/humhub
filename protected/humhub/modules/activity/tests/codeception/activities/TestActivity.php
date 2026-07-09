<?php

namespace humhub\modules\activity\tests\codeception\activities;

use humhub\modules\activity\components\BaseActivity;

class TestActivity extends BaseActivity
{
    protected function getMessage(array $params): string
    {
        return 'Content of no view activity';
    }
}
