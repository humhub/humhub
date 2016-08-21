<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\fixtures\modules\custom_pages\template;

use yii\test\ActiveFixture;

class TemplateFixture extends ActiveFixture
{

    public $modelClass = 'humhub\modules\custom_pages\modules\template\models\Template';
    public $dataFile = '@custom_pages/tests/codeception/fixtures/data/template.php';
    
     public function afterLoad()
    {
        parent::afterLoad();
        $this->db->createCommand()->setSql('SET FOREIGN_KEY_CHECKS = 1')->execute();
    }

}
