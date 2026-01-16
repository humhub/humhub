<?php

namespace humhub\modules\activity\tests\codeception\activities;

use humhub\modules\activity\components\BaseActivity;

class TestActivity extends BaseActivity
{

    public function getAsText(array $params = []): string
    {
        return 'Content of no view activity';
    }
}
