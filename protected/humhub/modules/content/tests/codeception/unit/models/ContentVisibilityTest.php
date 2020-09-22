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
use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;

use humhub\modules\space\models\Space;
use humhub\modules\content\models\Content;
use Yii;

class ContentVisibilityTest extends ContentModelTest
{

    public function testDefaultVisibilityPrivateSpace()
    {
        $this->space->visibility = Space::VISIBILITY_NONE;

        $newModel = new TestContent($this->space, [
            'message' => 'Test'
        ]);

        $this->assertTrue($newModel->save());
        $this->assertEquals($newModel->content->visibility, Content::VISIBILITY_PRIVATE);
    }

    public function testDefaultVisibilityProtectedSpace()
    {
        $this->space->visibility = Space::VISIBILITY_REGISTERED_ONLY;

        $newModel = new TestContent($this->space, [
            'message' => 'Test'
        ]);

        $this->assertTrue($newModel->save());
        $this->assertEquals($newModel->content->visibility, Content::VISIBILITY_PRIVATE);
    }

    public function testDefaultVisibilityPublicSpace()
    {
        $this->space->visibility = Space::VISIBILITY_ALL;

        $newModel = new TestContent($this->space, [
            'message' => 'Test'
        ]);

        $this->assertTrue($newModel->save());
        $this->assertEquals($newModel->content->visibility, Content::VISIBILITY_PRIVATE);
    }

    public function testCreatePublicContentOnPublicSpace()
    {
        $this->space->visibility = Space::VISIBILITY_ALL;

        $newModel = new TestContent($this->space, Content::VISIBILITY_PUBLIC, [
            'message' => 'Test'
        ]);

        $this->assertTrue($newModel->save());
        $this->assertEquals($newModel->content->visibility, Content::VISIBILITY_PUBLIC);
    }

    public function testCreatePublicContentOnProtectedSpace()
    {
        $this->space->visibility = Space::VISIBILITY_REGISTERED_ONLY;

        $newModel = new TestContent($this->space, Content::VISIBILITY_PUBLIC, [
            'message' => 'Test'
        ]);

        $this->assertTrue($newModel->save());
        $this->assertEquals($newModel->content->visibility, Content::VISIBILITY_PUBLIC);
    }

    public function testCreateContentOnDefaultContentVisibilityPublic()
    {
        $this->space->visibility = Space::VISIBILITY_ALL;
        $this->space->default_content_visibility = Content::VISIBILITY_PUBLIC;

        $newModel = new TestContent($this->space, [
            'message' => 'Test'
        ]);

        $this->assertTrue($newModel->save());
        $this->assertEquals($newModel->content->visibility, Content::VISIBILITY_PUBLIC);
    }

    public function testCreateContentOnDefaultContentVisibilityPrivate()
    {
        $this->space->visibility = Space::VISIBILITY_ALL;
        $this->space->default_content_visibility = Content::VISIBILITY_PRIVATE;

        $newModel = new TestContent($this->space, [
            'message' => 'Test'
        ]);

        $this->assertTrue($newModel->save());
        $this->assertEquals($newModel->content->visibility, Content::VISIBILITY_PRIVATE);
    }

    /**
     * Make sure private spaces can not produce public content
     *
     * Visibility integrity check missing!
     *
     * @skip
     * @throws \yii\base\Exception
     */
    public function testCreatePublicContentOnPrivateSpace()
    {
        $this->space->visibility = Space::VISIBILITY_NONE;

        $newModel = new TestContent($this->space, Content::VISIBILITY_PUBLIC, [
            'message' => 'Test'
        ]);

        $this->assertTrue($newModel->save());
        $this->assertEquals($newModel->content->visibility, Content::VISIBILITY_PRIVATE);
    }
}
