<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\space\tests\codeception\fixtures;

use yii\test\ActiveFixture;

class SpaceModuleFixture extends ActiveFixture
{

    public $modelClass = 'humhub\modules\space\models\Module';
    public $dataFile = '@modules/space/tests/codeception/fixtures/data/space_module.php';

}
