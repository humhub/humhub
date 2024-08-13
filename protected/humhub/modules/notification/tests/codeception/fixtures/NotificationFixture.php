<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\tests\codeception\fixtures;

use yii\test\ActiveFixture;

class NotificationFixture extends ActiveFixture
{

    public $modelClass = 'humhub\modules\notification\models\Notification';
    public $dataFile = '@modules/notification/tests/codeception/fixtures/data/notification.php';
    
    public $depends = [
        'humhub\modules\user\tests\codeception\fixtures\GroupUserFixture'
    ];

}
