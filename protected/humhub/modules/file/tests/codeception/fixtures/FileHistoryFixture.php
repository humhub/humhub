<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\file\tests\codeception\fixtures;

use humhub\modules\file\models\FileHistory;
use yii\test\ActiveFixture;

class FileHistoryFixture extends ActiveFixture
{

    public $modelClass = FileHistory::class;
    public $dataFile = '@file/tests/codeception/fixtures/data/file-history.php';

}
