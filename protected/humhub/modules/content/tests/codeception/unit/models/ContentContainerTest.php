<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content;

use humhub\libs\UUID;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\user\models\User;
use modules\content\tests\codeception\_support\ContentModelTest;

class ContentContainerTest extends ContentModelTest
{
    public function testUniqueGuid()
    {
        $user = User::findOne(['id' => 1]);
        $contentContainer = new ContentContainer(['guid' => $user->guid]);
        $contentContainer->setPolymorphicRelation($user);

        $this->assertFalse($contentContainer->save());
        $this->assertNotEmpty($contentContainer->getErrors('guid'));
    }

    public function testUniqueModel()
    {
        $user = User::findOne(['id' => 1]);
        $contentContainer = new ContentContainer(['guid' => UUID::v4()]);
        $contentContainer->setPolymorphicRelation($user);


        $this->assertFalse($contentContainer->save());
        $this->assertNotEmpty($contentContainer->getErrors('pk'));
    }

    public function testGuid()
    {
        $user = User::findOne(['id' => 1]);

        // make sure we have a fresh ID and GUID
        $user->id = 9;
        $user->guid = UUID::v4();

        $contentContainer = new ContentContainer();
        $contentContainer->setPolymorphicRelation($user);

        $this->assertFalse($contentContainer->save());
        $this->assertNotEmpty($contentContainer->getErrors('guid'));

        $user = User::findOne(['id' => 1]);

        // make user appear as new
        $user->setOldAttributes(null);
        $user->id = null;
        $user->guid = null;
        $user->username = "SomeNewUser";
        $user->email = "SomeNewUser@example.com";
        $user->populateRelation('contentContainerRecord', null);

        $this->assertTrue($user->save());
        $this->assertEmpty($user->getErrors('guid'));
        $this->assertEmpty($user->contentContainerRecord->getErrors('guid'));
    }

    public function testModelRequired()
    {
        $contentContainer = new ContentContainer();

        $this->assertFalse($contentContainer->save());
        $this->assertNotEmpty($contentContainer->getErrors('class'));
    }

    public function testInvalidModel()
    {
        $contentContainer = new ContentContainer();
        $contentContainer->setPolymorphicRelation(Content::findOne(['id' => 1]));

        $this->assertFalse($contentContainer->save());
        $this->assertNotEmpty($contentContainer->getErrors('class'));
    }

    public function testFindByGuid()
    {
        $user = User::findOne(['id' => 1]);
        $userRecord = ContentContainer::findRecord($user->guid);

        $this->assertInstanceOf(User::class, $userRecord);
        $this->assertEquals($user->id, $userRecord->id);
        $this->assertEquals($user->contentcontainer_id, $userRecord->contentcontainer_id);
    }

    public function testFindByInvalidGuid()
    {
        $this->assertNull(ContentContainer::findRecord('xxx'));
    }
}
