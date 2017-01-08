<?php

namespace tests\codeception\unit\modules\comment\components;

use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\content\tests\codeception\unit\models\TestContent;

class ContentCreatedTest extends HumHubDbTestCase
{

    use Specify;

    /**
     * Create a Mock Content class and assign a notify user save it and check if an email was sent and test wallout.
     */
    public function testCreateComment()
    {
        $this->becomeUser('User2');
        
        $testContent = new TestContent(['message' => 'MyTestContent']);
        $testContent->content->notifyUsersOfNewContent = [\humhub\modules\user\models\User::findOne(['id' => 2])];

        $testContent->save();
        
        $this->assertMailSent(1, 'ContentCreated Notification Mail sent');
        
        $this->assertEquals('<div>Wallentry:MyTestContent</div>', trim($testContent->getWallOut()));
    }

}
