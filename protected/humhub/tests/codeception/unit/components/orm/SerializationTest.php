<?php

namespace humhub\tests\codeception\unit\components\orm;

use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;

class SerializationTest extends HumHubDbTestCase
{

    use Specify;

    public function testSerializePost()
    {
        $model = \humhub\modules\post\models\Post::findOne(['id' => 1]);
        $unserialized = unserialize(serialize($model));
        
        $this->assertEquals($unserialized->id, 1);
        $this->assertEquals($unserialized->message, 'User 1 Profile Post Private');
        $this->assertEquals($model->test, 'aaa');
        $this->assertEquals($unserialized->test, 'aaa');
    }

}
