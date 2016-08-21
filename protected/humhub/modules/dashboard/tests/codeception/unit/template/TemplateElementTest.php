<?php

namespace tests\codeception\unit\modules\custom_page\template;

use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\custom_pages\modules\template\models\Template;
use humhub\modules\custom_pages\modules\template\models\RichtextContent;
use humhub\modules\custom_pages\modules\template\models\TemplateInstance;
use humhub\modules\custom_pages\modules\template\models\OwnerContent;
use humhub\modules\custom_pages\modules\template\models\TemplateElement;

class TemplateElementTest extends HumHubDbTestCase
{

    use Specify;

    public $template;
    public $owner;
    public $element;
    public $element2;
    public $defaultContent1;

    public function setUp()
    {
        parent::setUp();
        $this->template = Template::findOne(['id' => 1]);
        $this->element = TemplateElement::findOne(['id' => 1]);
        $this->element2 = TemplateElement::findOne(['id' => 2]);
        
        $this->defaultContent1 = RichtextContent::findOne(['id' => 1]);
        
        $this->owner = TemplateInstance::findOne(['id' => 1]);
    }

    public function testRenderDefaultContent()
    {
        $result = $this->template->render($this->owner);

        $this->assertContains('<p>Default</p>', $result);
        $this->assertContains('data-template-element="test_content"', $result);
        $this->assertContains('data-template-owner="' . $this->owner->className() . '"', $result);
        $this->assertContains('data-template-content="' . RichtextContent::className() . '"', $result);
        $this->assertContains('data-template-empty="0"', $result);
    }

    public function testOverwriteDefaultContent()
    {
        $content = new RichtextContent();
        $content->content = '<p>Non Default</p>';

        $this->element->saveInstance($this->owner, $content);

        $result = $this->template->render($this->owner);

        $this->assertContains('<p>Non Default</p>', $result);
        $this->assertContains('data-template-element="test_content"', $result);
        $this->assertContains('data-template-owner="' . $this->owner->className() . '"', $result);
        $this->assertContains('data-template-content="' . RichtextContent::className() . '"', $result);
        $this->assertContains('data-template-empty="0"', $result);
        // Test empty element
        $this->assertContains('data-template-empty="1"', $result);
    }

    public function testOverwriteEmptyDefaultContent()
    {
        $content = new RichtextContent();
        $content->content = '<p>Non Default2</p>';

        $this->element2->saveInstance($this->owner, $content);

        $result = $this->template->render($this->owner);

        $this->assertContains('<p>Non Default2</p>', $result);
        $this->assertContains('data-template-element="test_text"', $result);
        $this->assertNotContains('data-template-empty="1"', $result);
    }

    public function testOverwriteOldContent()
    {
        $content = new RichtextContent();
        $content->content = '<p>Non Default2</p>';

        $this->element2->saveInstance($this->owner, $content);

        $content2 = new RichtextContent();
        $content2->content = '<p>Non Default New</p>';
        $content2->save();

        $this->element2->saveInstance($this->owner, $content2);

        $result = $this->template->render($this->owner);

        $this->assertContains('<p>Non Default New</p>', $result);
        $this->assertNull(RichtextContent::findOne(['id' => $content->id]));
    }

    public function testSaveAsDefaultContent()
    {
        $content = new RichtextContent();
        $content->content = '<p>Default2</p>';
        $content->save();
        $this->element->saveAsDefaultContent($content);

        $result = $this->template->render($this->owner);

        $this->assertContains('<p>Default2</p>', $result);
        // Get sure the old default content was removed
        $this->assertNull(RichtextContent::findOne(['id' => $this->defaultContent1->id]));
    }

    public function testUniqueTemplateElementName()
    {
        $newElement = new TemplateElement();
        $newElement->scenario = 'create';
        $newElement->name = 'test_content';
        $newElement->content_type = RichtextContent::className();
        $newElement->template_id = $this->template->id;
        $newElement->save();

        $this->assertTrue($newElement->hasErrors());
    }

    public function testGetDefaultContent()
    {
        $this->assertEquals($this->defaultContent1->id, $this->element->getDefaultContent()->getInstance()->id);
    }

    public function testDeleteElement()
    {
        $content = new RichtextContent();
        $content->content = '<p>Non Default</p>';
        $content->save();

        $content2 = new RichtextContent();
        $content2->content = '<p>Non Default2</p>';
        $content2->save();

        $defaultContent = $this->element->getDefaultContent();

        $this->assertNotNull(OwnerContent::findOne(['id' => $defaultContent->id]));

        $this->element->saveInstance($this->owner, $content);
        $this->element2->saveInstance($this->owner, $content2);
        $this->element->delete();

        $this->assertNull(OwnerContent::findOne(['id' => $defaultContent->id]));
        $this->assertNull(RichtextContent::findOne(['id' => $content->id]));
        
        $this->assertNotNull(RichtextContent::findOne(['id' => $content2->id]));
        
        $this->assertNull($this->template->getElement('test_content'));
        $this->assertNotNull($this->template->getElement('test_text'));
    }
    
    public function testDeleteTemplate()
    {
        $content = new RichtextContent();
        $content->content = '<p>Non Default</p>';
        $content->save();

        $content2 = new RichtextContent();
        $content2->content = '<p>Non Default2</p>';
        $content2->save();

        $defaultContent = $this->element->getDefaultContent();

        $this->assertNotNull(OwnerContent::findOne(['id' => $defaultContent->id]));

        $this->element->saveInstance($this->owner, $content);
        $this->element2->saveInstance($this->owner, $content2);
        
        $this->assertFalse($this->template->delete());

        $this->owner->delete();
        
        $this->assertEquals('1', $this->template->delete());
        
        $this->assertNull(OwnerContent::findOne(['id' => $defaultContent->id]));
        $this->assertNull(RichtextContent::findOne(['id' => $content->id]));
    }
}
