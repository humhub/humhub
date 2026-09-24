<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace tests\codeception\unit\modules\content\widgets;

use humhub\modules\content\widgets\DeleteLink;
use humhub\modules\content\widgets\EditLink;
use humhub\modules\content\widgets\PermaLink;
use humhub\modules\content\widgets\PinLink;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\modules\content\widgets\stream\WallStreamEntryWidget;
use humhub\modules\content\widgets\WallEntryControlLink;
use humhub\modules\content\widgets\WallEntryControls;
use humhub\modules\post\models\Post;
use humhub\widgets\menu\WidgetMenuEntry;
use tests\codeception\_support\HumHubDbTestCase;
use Yii;

/**
 * The context menu of a content record as the stream renders it, since its core entries are
 * menu links rather than widgets ({@see WallEntryControls::createEntry()}).
 */
class WallEntryControlsTest extends HumHubDbTestCase
{
    private function controls(Post $post, ?WallStreamEntryOptions $options = null): WallEntryControls
    {
        $options ??= new WallStreamEntryOptions();

        return new WallEntryControls([
            'object' => $post,
            'wallEntryWidget' => Yii::createObject([
                'class' => $post->wallEntryClass,
                'model' => $post,
                'renderOptions' => $options,
            ]),
            'renderOptions' => $options,
        ]);
    }

    public function testCoreEntriesAreMenuLinksBuiltFromTheArrayDefinitions()
    {
        $this->becomeUser('Admin');
        $controls = $this->controls(Post::findOne(['id' => 1]));
        $controls->initControls();

        $classes = array_map(fn($entry) => $entry::class, $controls->getEntries());

        // `[DeleteLink::class, [...], [...]]` - the form modules keep passing - became the
        // entry itself, not a widget wrapper.
        $this->assertContains(DeleteLink::class, $classes);
        $this->assertContains(PermaLink::class, $classes);
        $this->assertNotContains(WidgetMenuEntry::class, $classes, 'no core entry is a widget any more');
    }

    public function testRendersTheSameAnchorsTheWidgetsDid()
    {
        $this->becomeUser('Admin');
        $html = $this->controls(Post::findOne(['id' => 1]))->run();

        $this->assertStringContainsString('data-action-click="content.permalink"', $html);
        $this->assertStringContainsString('data-content-permalink="', $html);
        $this->assertMatchesRegularExpression('/data-action-click="(adminDelete|delete)"/', $html);
        $this->assertStringContainsString('data-content-delete-url="', $html);
        $this->assertStringContainsString('class="dropdown-item', $html);
        $this->assertStringContainsString('Permalink', $html);

        // Each entry sits in an `<li>` of its own - the divider included, which renders its
        // own - and a hidden one leaves no empty item.
        $compact = preg_replace('/\s+/', '', $html);
        $this->assertStringNotContainsString('<li></li>', $compact);
        $this->assertStringNotContainsString('<li><li>', $compact);
    }

    public function testInlineEditRendersTheHiddenCancelAnchorToo()
    {
        $this->becomeUser('Admin');
        $post = Post::findOne(['id' => 1]);
        $link = new EditLink(['model' => $post, 'url' => '/edit', 'mode' => WallStreamEntryWidget::EDIT_MODE_INLINE]);

        $html = $link->render(['class' => 'dropdown-item']);

        $this->assertStringContainsString('stream-entry-edit-link', $html);
        $this->assertStringContainsString('data-action-click="edit"', $html);
        // The edit route, not the `#` of the anchor - both are called `url`.
        $this->assertStringContainsString('data-action-url="/edit"', $html);
        $this->assertStringContainsString('stream-entry-cancel-edit-link', $html);
        $this->assertStringContainsString('data-action-click="cancelEdit"', $html);

        // A client rendering the menu itself gets the entry alone, without the generated
        // widget id of the anchor.
        $htmlOptions = $link->describe()['htmlOptions'];
        $this->assertSame('edit', $htmlOptions['data-action-click']);
        $this->assertSame('/edit', $htmlOptions['data-action-url']);
        $this->assertArrayNotHasKey('id', $htmlOptions);
    }

    public function testAnEntryTheUserMayNotUseIsHidden()
    {
        $this->becomeUser('User2');
        // Admin's own post: User2 may neither edit nor delete it.
        $link = new DeleteLink(['content' => Post::findOne(['id' => 1])]);

        $this->assertFalse($link->isVisible());
        $this->assertSame('', $link->render());
    }

    public function testAnEntryIsStillPreventedByItsClass()
    {
        $this->becomeUser('Admin');
        $options = new WallStreamEntryOptions();
        // What `tasks` and `meeting` do: `'prevent' => [PinLink::class]` ends up here.
        $options->disableControlsEntry(PinLink::class);

        $controls = $this->controls(Post::findOne(['id' => 1]), $options);
        $controls->initControls();

        $classes = array_map(fn($entry) => $entry::class, $controls->getEntries());
        $this->assertNotContains(PinLink::class, $classes);
    }

    public function testALegacyWidgetDefinitionStaysAWidgetEntry()
    {
        $this->becomeUser('Admin');
        $controls = $this->controls(Post::findOne(['id' => 1]));
        $controls->addWidget(WallEntryControlLink::class, ['label' => 'Legacy'], ['sortOrder' => 5]);

        $legacy = array_values(array_filter(
            $controls->getEntries(),
            fn($entry) => $entry instanceof WidgetMenuEntry,
        ));
        $this->assertCount(1, $legacy);
        $this->assertSame(WallEntryControlLink::class, $legacy[0]->getEntryClass());
    }
}
