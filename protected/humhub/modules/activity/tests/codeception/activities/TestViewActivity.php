<?php

namespace humhub\modules\activity\tests\codeception\activities;

use humhub\modules\activity\components\BaseActivity;

/**
 * Description of TestActivity
 *
 * @author buddha
 */
class TestViewActivity extends BaseActivity
{
    public $moduleId = 'test';

    public $viewName = 'testWithView';
}
