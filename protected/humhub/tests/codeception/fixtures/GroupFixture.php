<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\fixtures;

use yii\test\ActiveFixture;

class GroupFixture extends ActiveFixture
{

    public $modelClass = 'humhub\modules\user\models\Group';
    public $dataFile = '@tests/codeception/fixtures/data/group.php';

    public $depends = [
        'tests\codeception\fixtures\GroupUserFixture'
    ];

}
