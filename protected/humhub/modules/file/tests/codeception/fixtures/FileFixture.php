<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\file\tests\codeception\fixtures;

use humhub\modules\file\models\File;
use yii\test\ActiveFixture;

class FileFixture extends ActiveFixture
{

    public $modelClass = File::class;
    public $dataFile = '@file/tests/codeception/fixtures/data/file.php';

}
