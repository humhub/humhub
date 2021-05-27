<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\tests\codeception\unit;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\ContentContainerTag;
use humhub\modules\content\models\ContentContainerTagRelation;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use tests\codeception\_support\HumHubDbTestCase;

class ContentContainerTagTest extends HumHubDbTestCase
{
    public function testCreation()
    {
        // Test Tag validation/constructor
        $this->assertTrue($this->createTestSpaceTag('First Space tag name'));
        $this->assertTrue($this->createTestUserTag('First User tag name'));

        // Test Tag db attributes
        $tag = ContentContainerTag::findOne(1);
        $this->assertEquals('First Space tag name', $tag->name);
        $this->assertEquals(Space::class, $tag->contentcontainer_class);

        // Test unique name per container class
        $this->assertFalse($this->createTestSpaceTag('First Space tag name'));
        $this->assertFalse($this->createTestUserTag('First User tag name'));

        // Other container class should be able to use the same name
        $this->assertTrue($this->createTestUserTag('First Space tag name'));
        $this->assertTrue($this->createTestSpaceTag('First User tag name'));
    }

    public function testFindByContainer()
    {
        $space2 = Space::findOne(2);
        $this->assertTrue($this->createTestSpaceTag('testTag1'));
        $this->assertTrue($this->createTestSpaceTag('testTag2'));
        $this->assertTrue($this->createTestSpaceTag('testTag3', $space2));

        $this->assertTrue($this->createTestSpaceTag('testTagA'));
        $this->assertTrue($this->createTestSpaceTag('testTagB'));
        $this->assertTrue($this->createTestSpaceTag('testTagC', $space2));

        $this->assertTrue($this->createTestSpaceTag('testTagX'));
        $this->assertTrue($this->createTestSpaceTag('testTagY'));
        $this->assertTrue($this->createTestSpaceTag('testTagZ', $space2));

        $this->assertEquals(3, ContentContainerTag::findByContainer($space2)->count());
    }

    public function testGetTags()
    {
        $space = Space::findOne(1);
        ContentContainerTagRelation::updateByContainer($space, ['Space Tag 1', 'Tag 2', 'Tag 3']);

        $this->assertEquals($space->getTags(), ['Space Tag 1', 'Tag 2', 'Tag 3']);

        $user = User::findOne(1);
        ContentContainerTagRelation::updateByContainer($user, ['User Tag 1', 'Tag 2']);

        $this->assertEquals($user->getTags(), ['User Tag 1', 'Tag 2']);

        $this->assertEquals(5, ContentContainerTag::find()->count());
        $this->assertEquals(3, ContentContainerTag::find()->where(['contentcontainer_class' => Space::class])->count());
        $this->assertEquals(2, ContentContainerTag::find()->where(['contentcontainer_class' => User::class])->count());
    }

    public function testTagDeletion()
    {
        $space = Space::findOne(1);
        ContentContainerTagRelation::updateByContainer($space, ['Tag for deletion']);
        $this->assertEquals(1, count($space->getTags()));

        ContentContainerTagRelation::deleteByContainer($space);
        $this->assertEquals(0, count($space->getTags()));
    }

    public function testTagDeletionByContainer()
    {
        $space2 = Space::findOne(2);
        $this->assertTrue($this->createTestSpaceTag('testTag1'));
        $this->assertTrue($this->createTestSpaceTag('testTag2'));
        $this->assertTrue($this->createTestSpaceTag('testTag3', $space2));

        $this->assertEquals(3, ContentContainerTag::find()->count());
        $this->assertEquals(1, ContentContainerTagRelation::find()->count());

        ContentContainerTagRelation::deleteByContainer($space2);

        $this->assertEquals(0, ContentContainerTagRelation::find()->count());
    }

    public function testDeleteAll()
    {
        $space2 = Space::findOne(2);
        $this->assertTrue($this->createTestSpaceTag('testTag1'));
        $this->assertTrue($this->createTestSpaceTag('testTag2'));
        $this->assertTrue($this->createTestSpaceTag('testTag3', $space2));
        $this->assertTrue($this->createTestSpaceTag('testTag4', $space2));

        $this->assertEquals(4, ContentContainerTag::find()->count());
        $this->assertEquals(2, ContentContainerTagRelation::find()->count());

        $this->assertEquals(4, ContentContainerTag::deleteAll());

        $this->assertEquals(0, ContentContainerTag::find()->count());
    }

    /**
     * @param string $name
     * @param string|null $containerClass
     * @param Space|User|null
     * @return bool
     */
    protected function createTestTag($name, $containerClass = null, $container = null)
    {
        if ($container instanceof ContentContainerActiveRecord) {
            $containerClass = get_class($container);
        }

        $tag = new ContentContainerTag();
        $tag->contentcontainer_class = $containerClass;
        $tag->name = $name;
        if (!$tag->save()) {
            return false;
        }

        if ($container !== null) {
            $tagRelation = new ContentContainerTagRelation();
            $tagRelation->contentcontainer_id = $container->contentcontainer_id;
            $tagRelation->tag_id = $tag->id;
            return $tagRelation->save();
        }

        return true;
    }

    /**
     * @param string $name
     * @param Space|null
     * @return bool
     */
    protected function createTestSpaceTag($name, $space = null)
    {
        return $this->createTestTag($name, Space::class, $space);
    }

    /**
     * @param string $name
     * @param User|null
     * @return bool
     */
    protected function createTestUserTag($name, $user = null)
    {
        return $this->createTestTag($name, User::class, $user);
    }

}
