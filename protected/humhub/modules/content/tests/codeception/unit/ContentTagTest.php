<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 24.07.2017
 * Time: 15:56
 */

namespace humhub\modules\content\tests\codeception\unit;


use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentTag;
use humhub\modules\content\models\ContentTagRelation;
use humhub\modules\space\models\Space;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\InvalidParamException;

class ContentTagTest extends HumHubDbTestCase
{
    public $space;

    public function testCreation()
    {
        // Test Tag validation/constructor
        $this->assertTrue($this->createTestTag('testTag1'));

        // Test Tag db attributes
        $tag = TestTag::findOne(1);
        $this->assertEquals('testTag1', $tag->name);
        $this->assertEquals($this->space->contentcontainer_id, $tag->contentcontainer_id);
        $this->assertEquals('test', $tag->moduleId);
        $this->assertEquals(TestTag::class, $tag->type);

        // Test unique module/container/name
        $this->assertFalse($this->createTestTag('testTag1'));

        // Other types should be able to use the same name
        $this->assertTrue($this->createOtherTestTag('testTag1'));

        // Other modules should be able to use the same name
        $this->assertTrue($this->createOtherModuleTestTag('testTag1'));

        // Other contentcontainer should be able to use the same name
        $space2 = Space::findOne(2);
        $this->assertTrue($this->createTestTag('testTag1', $space2));

        // Global tag should be able to use the same name
        $tag = new TestTagOtherModule(null, 'testTag1');
        $this->assertTrue($tag->save());
    }

    public function testFindByContainer()
    {
        $space2 = Space::findOne(2);
        $this->assertTrue($this->createTestTag('testTag1'));
        $this->assertTrue($this->createTestTag('testTag2'));
        $this->assertTrue($this->createTestTag('testTag3', $space2));

        $this->assertTrue($this->createOtherTestTag('testTagA'));
        $this->assertTrue($this->createOtherTestTag('testTagB'));
        $this->assertTrue($this->createOtherTestTag('testTagC', $space2));

        $this->assertTrue($this->createOtherModuleTestTag('testTagX'));
        $this->assertTrue($this->createOtherModuleTestTag('testTagY'));
        $this->assertTrue($this->createOtherModuleTestTag('testTagZ', $space2));

        $allSpace1Tags = ContentTag::findByContainer($this->space)->all();
        $this->assertEquals(6, count($allSpace1Tags));

        $allSpace2Tags = ContentTag::findByContainer($space2)->all();
        $this->assertEquals(3, count($allSpace2Tags));

        $typedSpace1Tags = TestTag::findByContainer($this->space)->all();
        $this->assertEquals(2, count($typedSpace1Tags));

        $typedSpace2Tags = TestTag::findByContainer($space2)->all();
        $this->assertEquals(1, count($typedSpace2Tags));

        $otherModuleTags = TestTagOtherModule::findByContainer($this->space)->all();
        $this->assertEquals(2, count($otherModuleTags));

        $otherModuleTagsS2 = TestTagOtherModule::findByContainer($space2)->all();
        $this->assertEquals(1, count($otherModuleTagsS2));

    }

    public function testTagDeletion()
    {
        $content = Content::findOne(1);
        $tag2 = new TestTagSameModule($content->getContainer(), 'test2');
        $tag2->save();
        $content->addTag($tag2);
        $this->assertEquals(1, count($content->tagRelations));

        $tag2->delete();
        $content->refresh();
        $this->assertEquals(0, count($content->tagRelations));

    }

    public function testContentDeletion()
    {
        $content = Content::findOne(1);
        $tag2 = new TestTagSameModule($content->getContainer(), 'test2');
        $tag2->save();
        $content->addTag($tag2);
        $this->assertEquals(1, ContentTagRelation::find()->count());

        $content->delete();
        $this->assertEquals(0, ContentTagRelation::find()->count());

    }

    public function testTagContentRelation()
    {
        $this->space = Space::findOne(['id' => 3]);
        $tag = new TestTag($this->space, 'test');
        $content = Content::findOne(1);

        try {
            $content->addTag($tag);
            $this->assertTrue(false);
        } catch(InvalidParamException $e) {
            // Tag was not saved
            $this->assertTrue(true);
        }

        $this->assertTrue($tag->save());

        try {
            $content->addTag($tag);
            $this->assertTrue(false);
        } catch(InvalidParamException $e) {
            // Tag assigned with invalid container_id
            $this->assertTrue(true);
        }

        $tag->contentcontainer_id = $content->contentcontainer_id;

        $this->assertTrue($content->addTag($tag));
        $this->assertEquals(1, count($content->tags));

        $this->assertTrue($content->addTag($tag));
        $this->assertEquals(1, count($content->tags));


        $tag2 = new TestTagSameModule($content->getContainer(), 'test2');
        $tag2->save();
        $content->addTag($tag2);
        $this->assertEquals(2, count($content->tags));

        $tag3 = new TestTagOtherModule($content->getContainer(), 'test3');
        $tag3->save();
        $content->addTag($tag3);
        $this->assertEquals(3, count($content->tags));

        $sameModuleTags = TestTagSameModule::findByContent($content)->all();
        $this->assertEquals(1, count($sameModuleTags));

        $tags = TestTag::findByContent($content)->all();
        $this->assertEquals(1, count($tags));

        TestTagSameModule::deleteContentRelations($content);
        $sameModuleTags = TestTagSameModule::findByContent($content)->all();
        $this->assertEquals(0, count($sameModuleTags));
        $this->assertEquals(2, count($content->tags));

        ContentTag::deleteContentRelations($content);
        $this->assertEquals(0, count($content->tags));
    }

    protected function createTestTag($name, $container = null)
    {
        $container = (!$container) ? $this->space : $container;

        if(!$container) {
            $container = $this->space = Space::findOne(['id' => 3]);
        }

        $tag = new TestTag($container, $name);
        return $tag->save();
    }

    protected function createOtherTestTag($name, $container = null)
    {
        $container = (!$container) ? $this->space : $container;

        if(!$container) {
            $container = $this->space = Space::findOne(['id' => 3]);
        }

        $tag = new TestTagSameModule($container, $name);
        return $tag->save();
    }

    protected function createOtherModuleTestTag($name, $container = null)
    {
        $container = (!$container) ? $this->space : $container;

        if(!$container) {
            $container = $this->space = Space::findOne(['id' => 3]);
        }

        $tag = new TestTagOtherModule($container, $name);
        return $tag->save();
    }


}