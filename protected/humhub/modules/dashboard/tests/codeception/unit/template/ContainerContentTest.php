<?php

namespace tests\codeception\unit\modules\custom_page\template;

use tests\codeception\_support\HumHubDbTestCase;
use Codeception\Specify;
use humhub\modules\custom_pages\modules\template\models\OwnerContent;
use humhub\modules\custom_pages\modules\template\models\TemplateInstance;
use humhub\modules\custom_pages\modules\template\models\Template;
use humhub\modules\custom_pages\modules\template\models\RichtextContent;
use humhub\modules\custom_pages\models\Page;
use humhub\modules\custom_pages\modules\template\models\ContainerContent;
use humhub\modules\custom_pages\modules\template\models\ContainerContentItem;
use humhub\modules\custom_pages\modules\template\models\ContainerContentDefinition;

class TemplateInstanceTest extends HumHubDbTestCase
{

    use Specify;

    public $owner2;
    public $owner1;
    public $page;

    public function setUp()
    {
        parent::setUp();
       
    }
    
    public function testDeleteContainerItem()
    {
        
        ContainerContentItem::findOne(['id' => 2])->delete();
        ContainerContentItem::findOne(['id' => 3])->delete();
        ContainerContentItem::findOne(['id' => 4])->delete();
        
        $this->assertNull(OwnerContent::findOne(['id' => 5]));
        $this->assertNull(OwnerContent::findOne(['id' => 6]));
        $this->assertNull(OwnerContent::findOne(['id' => 7]));
        

        $this->assertNull(RichtextContent::findOne(['id' => 3]));
        $this->assertNull(RichtextContent::findOne(['id' => 4]));
        $this->assertNull(RichtextContent::findOne(['id' => 5])); 

    }
    
    public function testDeleteContainerContent()
    {
        
        $container = ContainerContent::findOne(['id' => 2]);
        
        $this->assertEquals(3, $container->getItems()->count());
        
        $container->delete();
        
        $this->assertNull(ContainerContentDefinition::findOne(['id' => 2]));
        
        $this->assertNull(ContainerContentItem::findOne(['id' => 2]));
        $this->assertNull(ContainerContentItem::findOne(['id' => 3]));
        $this->assertNull(ContainerContentItem::findOne(['id' => 4]));
        
        $this->assertNull(OwnerContent::findOne(['id' => 5]));
        $this->assertNull(OwnerContent::findOne(['id' => 6]));
        $this->assertNull(OwnerContent::findOne(['id' => 7]));
        

        $this->assertNull(RichtextContent::findOne(['id' => 3]));
        $this->assertNull(RichtextContent::findOne(['id' => 4]));
        $this->assertNull(RichtextContent::findOne(['id' => 5])); 

    }
    
    public function testDeleteParentContainer()
    {
        
        $container = ContainerContent::findOne(['id' => 1]);
        
        $container->delete();
        
        $this->assertNull(ContainerContentItem::findOne(['id' => 2]));
        $this->assertNull(ContainerContentItem::findOne(['id' => 3]));
        $this->assertNull(ContainerContentItem::findOne(['id' => 4]));
        
        $this->assertNull(OwnerContent::findOne(['id' => 5]));
        $this->assertNull(OwnerContent::findOne(['id' => 6]));
        $this->assertNull(OwnerContent::findOne(['id' => 7]));
        

        $this->assertNull(RichtextContent::findOne(['id' => 3]));
        $this->assertNull(RichtextContent::findOne(['id' => 4]));
        $this->assertNull(RichtextContent::findOne(['id' => 5])); 

    }
    
    public function testDeletePage()
    {
        
        $page = Page::findOne(['id' => 2]);
        
        $page->delete();
        
        $this->assertNull(ContainerContentItem::findOne(['id' => 2]));
        $this->assertNull(ContainerContentItem::findOne(['id' => 3]));
        $this->assertNull(ContainerContentItem::findOne(['id' => 4]));
        
        $this->assertNull(OwnerContent::findOne(['id' => 5]));
        $this->assertNull(OwnerContent::findOne(['id' => 6]));
        $this->assertNull(OwnerContent::findOne(['id' => 7]));
        

        $this->assertNull(RichtextContent::findOne(['id' => 3]));
        $this->assertNull(RichtextContent::findOne(['id' => 4]));
        $this->assertNull(RichtextContent::findOne(['id' => 5])); 

    }
    
    public function testDeleteAll()
    {
        Page::findOne(['id' => 2])->delete();
        Page::findOne(['id' => 1])->delete();
        
        $this->assertEquals(0, OwnerContent::find()->where(['not', ['owner_model' => Template::className()]])->count());
        $this->assertEquals(0, TemplateInstance::find()->count());
        $this->assertEquals(1, RichtextContent::find()->count());
        $this->assertEquals(0, ContainerContentItem::find()->count());
        $this->assertEquals(0, ContainerContent::find()->count());
        
        $this->assertEquals(0, ContainerContentDefinition::find()->count());
        
        Template::findOne(['id' => 1])->delete();
        Template::findOne(['id' => 2])->delete();
        Template::findOne(['id' => 3])->delete();
        Template::findOne(['id' => 4])->delete();
        
        $this->assertEquals(0, RichtextContent::find()->count());
        $this->assertEquals(0, \humhub\modules\custom_pages\modules\template\models\TemplateElement::find()->count());
        

    }
}
