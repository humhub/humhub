<?php

namespace humhub\modules\activity\tests\codeception\activities;

use Yii;
use yii\helpers\Url;

/**
 * Description of TestActivity
 *
 * @author buddha
 */
class TestActivityDefaultLayout extends \humhub\modules\activity\components\BaseActivity {

    public $moduleId = 'test';

    public function html()
    {
        return 'Content of default layout activity';
    }
}
