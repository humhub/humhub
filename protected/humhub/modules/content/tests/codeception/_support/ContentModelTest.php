<?php


namespace modules\content\tests\codeception\_support;


use humhub\modules\content\models\Content;
use humhub\modules\content\tests\codeception\unit\TestContent;
use humhub\modules\space\models\Space;
use tests\codeception\_support\HumHubDbTestCase;

class ContentModelTest extends HumHubDbTestCase
{
    /**
     * @var TestContent
     */
    public $testModel;

    /**
     * @var Content
     */
    public $testContent;

    /**
     * @var Space
     */
    public $space;

    public function _before()
    {
        parent::_before();
        $this->becomeUser('User2');
        $this->space = Space::findOne(['id' => 2]);

        $this->testModel = new TestContent($this->space, Content::VISIBILITY_PUBLIC, [
            'message' => 'Test'
        ]);

        $this->assertTrue($this->testModel->save());

        $this->testContent = $this->testModel->content;
    }

}
