<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\assets\JqueryAutosizeAsset;
use humhub\components\View;
use humhub\modules\content\assets\ProseMirrorRichTextAsset;
use humhub\modules\content\events\RichTextPluginAssetsEvent;
use humhub\modules\content\widgets\richtext\RichText;
use humhub\modules\content\widgets\richtext\RichTextField;
use tests\codeception\_support\HumHubDbTestCase;
use yii\base\Event;

/**
 * Covers the context id of a rich text (`contextId`), which names where a text lives so plugins can act per
 * context, and the event modules use to load the assets of their rich text plugins.
 */
class RichTextContextTest extends HumHubDbTestCase
{
    public function testOutputWithoutContextIdHasNoContextData()
    {
        $this->assertStringNotContainsString('data-context-id', RichText::output('Test'));
    }

    public function testOutputHasContextIdData()
    {
        $this->assertStringContainsString(
            'data-context-id="comment"',
            RichText::output('Test', ['contextId' => 'comment']),
        );
    }

    public function testEditorHasContextIdData()
    {
        $this->assertStringContainsString(
            'data-context-id="wiki.page"',
            RichTextField::widget(['name' => 'test', 'value' => 'Test', 'contextId' => 'wiki.page']),
        );
        $this->assertStringNotContainsString(
            'data-context-id',
            RichTextField::widget(['name' => 'test', 'value' => 'Test']),
        );
    }

    public function testPluginAssetsAreRegisteredByEvent()
    {
        $handler = static function (RichTextPluginAssetsEvent $event) {
            $event->assets[] = JqueryAutosizeAsset::class;
        };
        Event::on(ProseMirrorRichTextAsset::class, ProseMirrorRichTextAsset::EVENT_REGISTER_PLUGIN_ASSETS, $handler);

        try {
            $view = new View();
            $this->assertArrayNotHasKey(JqueryAutosizeAsset::class, $view->assetBundles);

            ProseMirrorRichTextAsset::register($view);

            $this->assertArrayHasKey(JqueryAutosizeAsset::class, $view->assetBundles);
        } finally {
            Event::off(ProseMirrorRichTextAsset::class, ProseMirrorRichTextAsset::EVENT_REGISTER_PLUGIN_ASSETS, $handler);
        }
    }

    public function testPluginAssetsAreRegisteredAgainAfterTheViewIsCleared()
    {
        $count = 0;
        $handler = static function () use (&$count) {
            $count++;
        };
        Event::on(ProseMirrorRichTextAsset::class, ProseMirrorRichTextAsset::EVENT_REGISTER_PLUGIN_ASSETS, $handler);

        try {
            $view = new View();
            ProseMirrorRichTextAsset::register($view);
            $view->clear();
            ProseMirrorRichTextAsset::register($view);

            $this->assertSame(2, $count);
        } finally {
            Event::off(ProseMirrorRichTextAsset::class, ProseMirrorRichTextAsset::EVENT_REGISTER_PLUGIN_ASSETS, $handler);
        }
    }

    public function testPluginAssetsEventIsTriggeredOncePerView()
    {
        $count = 0;
        $handler = static function () use (&$count) {
            $count++;
        };
        Event::on(ProseMirrorRichTextAsset::class, ProseMirrorRichTextAsset::EVENT_REGISTER_PLUGIN_ASSETS, $handler);

        try {
            $view = new View();
            ProseMirrorRichTextAsset::register($view);
            ProseMirrorRichTextAsset::register($view);
            $this->assertSame(1, $count);

            ProseMirrorRichTextAsset::register(new View());
            $this->assertSame(2, $count);
        } finally {
            Event::off(ProseMirrorRichTextAsset::class, ProseMirrorRichTextAsset::EVENT_REGISTER_PLUGIN_ASSETS, $handler);
        }
    }
}
