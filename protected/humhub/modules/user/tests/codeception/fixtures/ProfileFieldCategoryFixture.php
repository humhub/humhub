<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\tests\codeception\fixtures;

use yii\test\ActiveFixture;

class ProfileFieldCategoryFixture extends ActiveFixture
{
    public $tableName = 'profile_field_category';
    public $modelClass = 'humhub\modules\user\models\ProfileFieldCategory';
    public $dataFile = '@modules/user/tests/codeception/fixtures/data/profile_field_category.php';
}
