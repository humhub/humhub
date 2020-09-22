<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\tests\codeception\fixtures;

use yii\test\ActiveFixture;

class FriendshipFixture extends ActiveFixture
{
    public $modelClass = 'humhub\modules\friendship\models\FriendShip';
    public $dataFile = '@modules/friendship/tests/codeception/fixtures/data/friendship.php';
}
