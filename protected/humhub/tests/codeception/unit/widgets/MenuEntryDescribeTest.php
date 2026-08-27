<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\tests\codeception\unit\widgets;

use humhub\modules\content\controllers\api\ControlsController;
use humhub\modules\content\widgets\stream\WallStreamEntryOptions;
use humhub\modules\content\widgets\WallEntryControlLink;
use humhub\modules\ui\menu\DescribableWidget;
use humhub\modules\ui\menu\DropdownDivider;
use humhub\modules\ui\menu\MenuEntry;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\ui\menu\WidgetMenuEntry;
use tests\codeception\_support\HumHubDbTestCase;
use yii\helpers\Html;

/**
 * The `describe()` chain that lets a server-side menu feed a client-rendered one — see
 * `humhub\modules\ui\menu\DescribableWidget` and
 * `humhub\modules\content\controllers\api\ControlsController`.
 */
class MenuEntryDescribeTest extends HumHubDbTestCase
{
    public function testMenuLinkDescribesItself()
    {
        $entry = new MenuLink([
            'id' => 'permalink',
            'label' => 'Permalink',
            'icon' => 'link',
            'url' => '/content/perma?id=3',
            'sortOrder' => 120,
        ]);

        $descriptor = $entry->describe();

        $this->assertSame('permalink', $descriptor['id']);
        $this->assertSame('Permalink', $descriptor['label']);
        $this->assertSame('link', $descriptor['icon']);
        $this->assertSame(120, $descriptor['sortOrder']);
        $this->assertSame('/content/perma?id=3', $descriptor['url']);
    }

    public function testDescribeIconStripsTheFaPrefix()
    {
        $this->assertSame('pencil', MenuLink::describeIcon('fa-pencil'));
        $this->assertSame('pencil', MenuLink::describeIcon('pencil'));
        $this->assertNull(MenuLink::describeIcon(''));
        $this->assertNull(MenuLink::describeIcon(null));
    }

    public function testDividerDescribesItself()
    {
        $descriptor = (new DropdownDivider(['sortOrder' => 25]))->describe();

        $this->assertTrue($descriptor['divider']);
        $this->assertSame(25, $descriptor['sortOrder']);
    }

    public function testPlainEntryIsNotDescribable()
    {
        $this->assertNull((new UndescribableTestEntry())->describe());
    }

    public function testControlLinkDescribesLabelIconAndLegacyActionOptions()
    {
        $entry = new WidgetMenuEntry([
            'widgetClass' => WallEntryControlLink::class,
            'widgetOptions' => [
                'label' => 'Delete',
                'icon' => 'fa-trash',
                'action' => 'content.delete',
            ],
            'sortOrder' => 300,
        ]);

        $descriptor = $entry->describe();

        $this->assertSame('Delete', $descriptor['label']);
        $this->assertSame('trash', $descriptor['icon']);
        $this->assertSame(300, $descriptor['sortOrder']);
        // The delegated document handler reads this off the DOM, so it has to survive into a
        // client-rendered anchor.
        $this->assertSame('content.delete', $descriptor['htmlOptions']['data-action-click']);
    }

    public function testWidgetEntryFallsBackToAKebabCaseIdDerivedFromItsClass()
    {
        $entry = new WidgetMenuEntry([
            'widgetClass' => WallEntryControlLink::class,
            'widgetOptions' => ['label' => 'Something'],
        ]);

        $this->assertSame('wall-entry-control-link', $entry->describe()['id']);
        $this->assertSame('content-topic-button', WidgetMenuEntry::describeIdFor(
            'humhub\\modules\\topic\\widgets\\ContentTopicButton',
        ));
    }

    public function testAnExplicitIdWinsOverTheClassFallback()
    {
        $entry = new WidgetMenuEntry([
            'id' => 'my-entry',
            'widgetClass' => WallEntryControlLink::class,
            'widgetOptions' => ['label' => 'Something'],
        ]);

        $this->assertSame('my-entry', $entry->describe()['id']);
    }

    public function testAControlLinkThatPreventsItsOwnRenderIsNotDescribed()
    {
        $entry = new WidgetMenuEntry([
            'widgetClass' => PreventedTestControlLink::class,
            'widgetOptions' => ['label' => 'Hidden'],
        ]);

        $this->assertNull($entry->describe());
    }

    /**
     * The load-bearing safety net: a subclass that builds its own markup cannot be described
     * from the base class' properties, so it must fall through to the HTML path instead of
     * yielding an entry with an empty label or a dead `#` link.
     */
    public function testASubclassRenderingItsOwnLinkIsNotDescribed()
    {
        $entry = new WidgetMenuEntry([
            'widgetClass' => OwnLinkTestControlLink::class,
            'widgetOptions' => ['label' => 'Ignored'],
        ]);

        $this->assertNull($entry->describe());
    }

    public function testASubclassMayDescribeItselfAnyway()
    {
        $entry = new WidgetMenuEntry([
            'widgetClass' => SelfDescribingTestControlLink::class,
            'widgetOptions' => [],
        ]);

        $this->assertSame('Own label', $entry->describe()['label']);
    }

    public function testANonDescribableWidgetEntryIsNotDescribed()
    {
        $entry = new WidgetMenuEntry([
            'widgetClass' => \humhub\modules\content\widgets\DeleteLink::class,
            'widgetOptions' => [],
        ]);

        $this->assertNull($entry->describe());
    }

    /**
     * The endpoint's `suppress` names are a public API (a host island spells `edit`, not
     * `EditLink`), and each one has to reach a real method. A rename on either side would
     * otherwise silently stop suppressing, and the host would get a duplicate entry.
     */
    public function testEverySuppressibleEntryNameMapsToARealOption()
    {
        $reflection = new \ReflectionClass(ControlsController::class);
        $names = $reflection->getConstant('SUPPRESSIBLE');

        $this->assertNotEmpty($names);

        $options = new WallStreamEntryOptions();
        foreach ($names as $name => $method) {
            $this->assertTrue(
                method_exists($options, $method),
                "Suppressible entry '$name' maps to missing method WallStreamEntryOptions::$method()",
            );
        }
    }

    public function testAnUnknownWidgetClassIsNotDescribed()
    {
        $entry = new WidgetMenuEntry(['widgetClass' => 'Not\\A\\Class']);

        $this->assertNull($entry->describe());
    }
}

class UndescribableTestEntry extends MenuEntry
{
    protected function renderEntry($extraHtmlOptions = [])
    {
        return '<li>plain</li>';
    }
}

class PreventedTestControlLink extends WallEntryControlLink
{
    public function preventRender()
    {
        return true;
    }
}

class OwnLinkTestControlLink extends WallEntryControlLink
{
    protected function renderLink()
    {
        return Html::a('Built elsewhere', '/somewhere', $this->options);
    }
}

class SelfDescribingTestControlLink extends WallEntryControlLink implements DescribableWidget
{
    protected function renderLink()
    {
        return Html::a('Own label', '/somewhere', $this->options);
    }

    public function describeMenuEntry(): ?array
    {
        return ['label' => 'Own label', 'url' => '/somewhere'];
    }
}
