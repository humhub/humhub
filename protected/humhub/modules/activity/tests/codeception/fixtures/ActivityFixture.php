<?php
namespace humhub\modules\activity\tests\codeception\fixtures;

use yii\test\ActiveFixture;

class ActivityFixture extends ActiveFixture
{
    public $modelClass = 'humhub\modules\activity\models\Activity';
    public $dataFile = '@modules/activity/tests/codeception/fixtures/data/activity.php';
}
