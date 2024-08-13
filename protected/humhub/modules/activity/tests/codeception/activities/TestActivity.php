<?php

namespace humhub\modules\activity\tests\codeception\activities;

use Yii;
use yii\helpers\Url;

/**
 * Description of TestActivity
 *
 * @author buddha
 */
class TestActivity extends \humhub\modules\activity\components\BaseActivity {

    public $moduleId = 'test';

    public $viewName = 'testNoView';

    public function html()
    {
        return 'Content of no view activity';
    }

    public function getUrl()
    {
        return Url::toRoute(['/user/account/edit']);
    }
}
