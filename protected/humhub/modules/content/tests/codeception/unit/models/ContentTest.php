<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace tests\codeception\unit\modules\content;

use humhub\modules\content\tests\codeception\unit\TestContent;
use modules\content\tests\codeception\_support\ContentModelTest;

use humhub\modules\space\models\Space;
use humhub\modules\content\models\Content;
use Yii;

class ContentTest extends ContentModelTest
{
    public function testContentDefaults()
    {
        $this->assertNotEmpty($this->testContent->id);
        $this->assertNotEmpty($this->testContent->guid);
        $this->assertEquals($this->testContent->object_model, TestContent::class);
        $this->assertEquals($this->testContent->object_id, $this->testModel->id);
        $this->assertEquals($this->testContent->visibility, Content::VISIBILITY_PUBLIC);
        $this->assertEquals($this->testContent->pinned, 0);
        $this->assertEquals($this->testContent->archived, 0);
        $this->assertNotEmpty($this->testContent->created_at);
        $this->assertEquals($this->testContent->created_by, Yii::$app->user->id);
        $this->assertIsString($this->testContent->updated_at);
        $this->assertEquals($this->testContent->updated_by, Yii::$app->user->id);
        $this->assertEquals($this->testContent->updated_at, $this->testContent->created_at);
        $this->assertEquals($this->testContent->stream_channel,Content::STREAM_CHANNEL_DEFAULT);
        $this->assertEquals($this->testContent->contentcontainer_id,$this->space->contentcontainer_id);
    }

    public function testInvalidPolymorphicRelation1()
    {
        $testContent = new TestContent($this->space, Content::VISIBILITY_PUBLIC, [
            'message' => 'Test'
        ]);

        $testContent->content->object_model = null;

        try {
            $testContent->save();
            $this->assertTrue(false, 'Content should not be saved!');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testInvalidPolymorphicRelation2()
    {
        $testContent = new TestContent($this->space, Content::VISIBILITY_PUBLIC, [
            'message' => 'Test'
        ]);

        $testContent->content->object_id = null;

        try {
            $testContent->save();
            $this->assertTrue(false, 'Content should not be saved!');
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    public function testContentRelation()
    {
        $loadedContent = Content::Get(TestContent::class, $this->testModel->id);
        $this->assertTrue($loadedContent->content instanceof Content);
        $this->assertEquals($loadedContent->content->id, $this->testModel->content->id);
        $this->assertTrue($loadedContent->content->container instanceof Space);
        $this->assertEquals($loadedContent->content->container->id, $this->space->id);
        $this->assertEquals($loadedContent->content->contentContainer->id, $this->space->contentcontainer_id);
    }

    public function testContentGet()
    {
        $loadedContent = Content::Get(TestContent::class, $this->testModel->id);
        $this->assertEquals($loadedContent->id, $this->testModel->id);
        $this->assertEquals($loadedContent->content->id, $this->testModel->content->id);
    }

    public function testContentGetNotFound()
    {
        $loadedContent = Content::Get(TestContent::class, 300);
        $this->assertNull($loadedContent);
    }

    public function testContentGetInvalidClass()
    {
        $loadedContent = Content::Get('SomeNonExistingClass', 300);
        $this->assertNull($loadedContent);
    }
}
