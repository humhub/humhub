# Sidebars and Snippets

A *snippet* is a self-contained panel that lives in a sidebar — used on the dashboard, space layout, directory and similar surfaces. The sidebar is a [widget stack](ui-widgets.md#widget-stacks), so snippets are added by listening to that stack's `EVENT_INIT`.

## Adding content from inside a view

If you control the layout's main view, the quickest way to inject sidebar content is a [Yii view block](https://www.yiiframework.com/doc/guide/2.0/en/structure-views#using-blocks):

```php
<?php $this->beginBlock('sidebar'); ?>
    Your sidebar content
<?php $this->endBlock(); ?>
```

The surrounding layout renders the `sidebar` block.

## Adding a snippet from a module

For sidebars defined elsewhere (the dashboard sidebar, the space sidebar, …), listen for the sidebar widget's `EVENT_INIT` and call `addWidget()`:

```php
use johndoe\example\widgets\MySnippet;

public static function onSpaceSidebarInit($event)
{
    $space = $event->sender->space;

    if ($space->moduleManager->isEnabled('example')) {
        $event->sender->addWidget(
            MySnippet::class,
            ['contentContainer' => $space],
            ['sortOrder' => 100]
        );
    }
}
```

`addWidget()` takes three arguments: the widget class, the widget config, and stack-level options (only `sortOrder` is interpreted today).

### Snippet markup

Snippets follow the bootstrap panel structure. `PanelMenu` is the convention for the gear-style menu in the top-right of the panel:

```php
<?php
use humhub\widgets\PanelMenu;

$extraMenus = '<li><a href="' . $url . '"><i class="fa fa-arrow-circle-right"></i> '
    . Yii::t('ExampleModule.base', 'Extra menu item') . '</a></li>';
?>
<div class="panel example-snippet" id="example-snippet">
    <div class="panel-heading">
        <i class="fa fa-home"></i> <?= Yii::t('ExampleModule.base', '<strong>Example</strong> snippet') ?>
        <?= PanelMenu::widget(['id' => 'example-snippet', 'extraMenus' => $extraMenus]) ?>
    </div>

    <div class="panel-body">
        <?php /* snippet body */ ?>
    </div>
</div>
```

## Registering the listener

Wire the event handler in your module's `config.php`. The pattern is the same for every sidebar — only the sidebar class changes.

### Dashboard sidebar

```php
use humhub\modules\dashboard\widgets\Sidebar;
use johndoe\example\Events;

return [
    'id' => 'example',
    // ...
    'events' => [
        [
            'class' => Sidebar::class,
            'event' => Sidebar::EVENT_INIT,
            'callback' => [Events::class, 'onDashboardSidebarInit'],
        ],
    ],
];
```

### Space sidebar

```php
use humhub\modules\space\widgets\Sidebar;

'events' => [
    [
        'class' => Sidebar::class,
        'event' => Sidebar::EVENT_INIT,
        'callback' => [Events::class, 'onSpaceSidebarInit'],
    ],
],
```

### Directory sidebar (legacy)

```php
use humhub\modules\directory\widgets\Sidebar;

'events' => [
    [
        'class' => Sidebar::class,
        'event' => Sidebar::EVENT_INIT,
        'callback' => [Events::class, 'onDirectorySidebarInit'],
    ],
],
```
