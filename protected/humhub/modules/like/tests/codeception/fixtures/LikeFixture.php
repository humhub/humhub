<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\like\tests\codeception\fixtures;

use yii\test\ActiveFixture;

class LikeFixture extends ActiveFixture
{

    public $modelClass = 'humhub\modules\like\models\Like';
    public $dataFile = '@modules/like/tests/codeception/fixtures/data/like.php';

}
