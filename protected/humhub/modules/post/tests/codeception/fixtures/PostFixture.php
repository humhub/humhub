<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\post\tests\codeception\fixtures;

use yii\test\ActiveFixture;

class PostFixture extends ActiveFixture
{

    public $modelClass = 'humhub\modules\post\models\Post';
    public $dataFile = '@modules/post/tests/codeception/fixtures/data/post.php';

}
