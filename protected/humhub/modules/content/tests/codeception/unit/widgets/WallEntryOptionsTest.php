<?php


namespace humhub\modules\content\tests\codeception\unit\widgets;


use humhub\modules\content\helpers\ContentContainerHelper;
use humhub\modules\content\widgets\DeleteLink;
use humhub\modules\content\widgets\stream\StreamEntryOptions;
use humhub\modules\content\widgets\stream\StreamEntryWidget;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\modules\content\widgets\WallEntryControls;
use humhub\modules\post\models\Post;
use humhub\modules\post\widgets\WallEntry;
use humhub\modules\ui\menu\WidgetMenuEntry;
use tests\codeception\_support\HumHubDbTestCase;

class WallEntryOptionsTest extends HumHubDbTestCase
{
    public function testConstructorWithBaseOptions()
    {
        $streamOptions = (new StreamEntryOptions)->viewContext('test')
            ->overwriteWidgetClass(WallEntry::class);

        $wallStreamEntryOptions = new StreamEntryOptions($streamOptions);
        $this->assertTrue($wallStreamEntryOptions->isViewContext('test'));
        $this->assertEquals(WallEntry::class, $wallStreamEntryOptions->getStreamEntryWidgetClass());
    }


    public function testDisableControlsEdit()
    {
        $this->testDisableControlsItem('Edit',
            (new WallStreamEntryOptions)->disableControlsEntryEdit());
    }

    public function testDisableControlsDelete()
    {
        $this->testDisableControlsItem('Delete',
            (new WallStreamEntryOptions)->disableControlsEntryDelete());
    }

    public function testDisableControlsTopic()
    {
        $this->testDisableControlsItem('Topics',
            (new WallStreamEntryOptions)->disableControlsEntryTopics());
    }

    public function testDisableControlsPermalink()
    {
        $this->testDisableControlsItem('Permalink',
            (new WallStreamEntryOptions)->disableControlsEntryPermalink());
    }

    public function testDisableControlsSwitchVisibility()
    {
        $this->testDisableControlsItem('Change to "Public"',
            (new WallStreamEntryOptions)->disableControlsEntrySwitchVisibility());
    }

    public function testDisableControlsSwitchNotification()
    {
        $this->testDisableControlsItem('Turn on notifications',
            (new WallStreamEntryOptions)->disableControlsEntrySwitchNotification());
    }

    public function testDisableControlsPin()
    {
        $this->testDisableControlsItem('Pin to top',
            (new WallStreamEntryOptions)->disableControlsEntryPin());
    }

    public function testDisableControlsMove()
    {
        $this->testDisableControlsItem('Move content',
            (new WallStreamEntryOptions)->disableControlsEntryMove());
    }

    public function testDisableControlsArchive()
    {
        $this->testDisableControlsItem('Move to archive',
            (new WallStreamEntryOptions)->disableControlsEntryArchive());
    }

    public function testDisableControlsMenu()
    {
        $this->assertWallEntryControlsNotContains('preferences',  (new WallStreamEntryOptions)->disableControlsMenu());
    }

    public function testDisableAddonsMenu()
    {
        $this->assertWallEntryContains('stream-entry-addons',  new WallStreamEntryOptions);
        $this->assertWallEntryNotContains('stream-entry-addons', (new WallStreamEntryOptions)->disableAddons());
    }

    public function testDisableCommentAddonsMenu()
    {
        $this->assertWallEntryContains('comment-container',  new WallStreamEntryOptions);
        $this->assertWallEntryNotContains('comment-container', (new WallStreamEntryOptions)->disableCommentAddon());
    }

    public function testDisableWallEntryLinksAddonsMenu()
    {
        $this->assertWallEntryContains('wall-entry-links',  new WallStreamEntryOptions);
        $this->assertWallEntryNotContains('wall-entry-links', (new WallStreamEntryOptions)->disableWallEntryLinks());
    }

    public function testSearchStreamDoesOnlyIncludePermalink()
    {
        $this->assertWallEntryControlsContains('Permalink',  (new WallStreamEntryOptions)->viewContext(WallStreamEntryOptions::VIEW_CONTEXT_SEARCH));
        $this->assertWallEntryControlsNotContains('Delete',  (new WallStreamEntryOptions)->viewContext(WallStreamEntryOptions::VIEW_CONTEXT_SEARCH));
        $this->assertWallEntryControlsNotContains('Edit',  (new WallStreamEntryOptions)->viewContext(WallStreamEntryOptions::VIEW_CONTEXT_SEARCH));
        $this->assertWallEntryControlsNotContains('Move to archive',  (new WallStreamEntryOptions)->viewContext(WallStreamEntryOptions::VIEW_CONTEXT_SEARCH));
        $this->assertWallEntryControlsNotContains('Move',  (new WallStreamEntryOptions)->viewContext(WallStreamEntryOptions::VIEW_CONTEXT_SEARCH));
        $this->assertWallEntryControlsNotContains('Pin to top',  (new WallStreamEntryOptions)->viewContext(WallStreamEntryOptions::VIEW_CONTEXT_SEARCH));
        $this->assertWallEntryControlsNotContains('Turn on notifications',  (new WallStreamEntryOptions)->viewContext(WallStreamEntryOptions::VIEW_CONTEXT_SEARCH));
        $this->assertWallEntryControlsNotContains('Change to "Public"',  (new WallStreamEntryOptions)->viewContext(WallStreamEntryOptions::VIEW_CONTEXT_SEARCH));
    }

    private function testDisableControlsItem($searchStr, $renderOptions)
    {
        $this->becomeUser('admin');

        $model = Post::findOne(['id' => 1]);

        // In order for pin controls to work
        ContentContainerHelper::setCurrent($model->content->container);

        $wallEntry = new WallEntry(['model' => $model]);
        $result = WallEntryControls::widget(['object' => $model, 'wallEntryWidget' => $wallEntry]);
        $this->assertStringContainsString($searchStr, $result);

        $wallEntry = new WallEntry(['model' => $model, 'renderOptions' => $renderOptions]);

        $this->assertStringNotContainsString($searchStr, WallEntryControls::widget([
            'object' => $model,
            'wallEntryWidget' => $wallEntry
        ]));
    }

    public function testWallEntryIsUsingOwnOptionInstance()
    {
        $this->becomeUser('admin');

        $model = Post::findOne(['id' => 1]);

        $renderOptions = new WallStreamEntryOptions();
        $renderOptions->viewContext(StreamEntryOptions::VIEW_CONTEXT_MODAL);
        $renderOptions->disableControlsEntryEdit();

        $wallEntryA = new WallEntry(['model' => $model, 'renderOptions' => $renderOptions]);
        $wallEntryB = new WallEntry(['model' => $model, 'renderOptions' => $renderOptions]);

        $wallEntryA->renderOptions->disableControlsEntryDelete();

        static::assertEquals(StreamEntryOptions::VIEW_CONTEXT_MODAL, $wallEntryA->renderOptions->getViewContext());
        static::assertEquals(StreamEntryOptions::VIEW_CONTEXT_MODAL, $wallEntryB->renderOptions->getViewContext());

        static::assertTrue($wallEntryA->renderOptions->isContextMenuEntryDisabled(new WidgetMenuEntry(['widgetClass' => DeleteLink::class])));
        static::assertFalse($wallEntryB->renderOptions->isContextMenuEntryDisabled(new WidgetMenuEntry(['widgetClass' => DeleteLink::class])));

        $wallEntryB->renderOptions->viewContext(StreamEntryOptions::VIEW_CONTEXT_DASHBOARD);

        static::assertEquals(StreamEntryOptions::VIEW_CONTEXT_MODAL, $wallEntryA->renderOptions->getViewContext());
        static::assertEquals(StreamEntryOptions::VIEW_CONTEXT_DASHBOARD, $wallEntryB->renderOptions->getViewContext());
    }

    private function assertWallEntryControlsContains($searchStr, $renderOptions)
    {
        $this->becomeUser('admin');

        $model = Post::findOne(['id' => 1]);

        // In order for pin controls to work
        ContentContainerHelper::setCurrent($model->content->container);

        $wallEntry = new WallEntry(['model' => $model, 'renderOptions' => $renderOptions]);

        $this->assertStringContainsString($searchStr, WallEntryControls::widget([
            'object' => $model,
            'wallEntryWidget' => $wallEntry
        ]));
    }

    private function assertWallEntryControlsNotContains($searchStr, $renderOptions)
    {
        $this->becomeUser('admin');

        $model = Post::findOne(['id' => 1]);

        // In order for pin controls to work
        ContentContainerHelper::setCurrent($model->content->container);

        $wallEntry = new WallEntry(['model' => $model, 'renderOptions' => $renderOptions]);

        $this->assertStringNotContainsString($searchStr, WallEntryControls::widget([
            'object' => $model,
            'wallEntryWidget' => $wallEntry
        ]));
    }

    private function assertWallEntryNotContains($searchStr, $renderOptions)
    {
        $this->becomeUser('admin');

        $model = Post::findOne(['id' => 1]);

        // In order for pin controls to work
        ContentContainerHelper::setCurrent($model->content->container);

        $this->assertStringNotContainsString($searchStr, StreamEntryWidget::renderStreamEntry($model, $renderOptions));
    }

    private function assertWallEntryContains($searchStr, $renderOptions)
    {
        $this->becomeUser('admin');

        $model = Post::findOne(['id' => 1]);

        // In order for pin controls to work
        ContentContainerHelper::setCurrent($model->content->container);

        $this->assertStringContainsString($searchStr, StreamEntryWidget::renderStreamEntry($model, $renderOptions));
    }
}
