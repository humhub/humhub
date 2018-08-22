Sidebars and Snippets
======================

Snippets are self contained panels which can be added to the sidebar, for example the _space_, _directory_, or _dashboard_ layout.


## Adding content to the sidebar

You can use the [Yii2 View Blocks](https://www.yiiframework.com/doc/guide/2.0/en/structure-views#using-blocks) feature to move content from your view file into the sidebar as follows:

```php
<?php $this->beginBlock('sidebar'); ?>
Your sidebar content
<?php $this->endBlock(); ?>
```

See also: [[humhub\modules\ui\view\components\View|getSidebar]]


## Event Handlers

You can use the [[humhub\widgets\BaseMenu::EVENT_INIT]] event in order to append `snippets` to a sidebar.
Your event handler will look something like this:

```php
public static function onSpaceSidebarInit($event)
{
    $space = $event->sender->space;
    $settings = SnippetModuleSettings::instantiate();

    if ($space->isModuleEnabled('mymodule')) {
        if ($settings->showUpcomingEventsSnippet()) {
            $event->sender->addWidget(MySnippet::class, ['contentContainer' => $space], ['sortOrder' => $settings->upcomingEventsSnippetSortOrder]);
        }
    }
}
```

The following snippet view rendered by your `MySnippet::class` appends an extra meu item to the `PanelMenu`.

```php
<?php
use humhub\widgets\PanelMenu;

$extraMenus = '<li><a href="'.$url.'"><i class="fa fa-arrow-circle-right"></i> '. Yii::t('MyModule.base', 'Some extra snippet men item') .'</a></li>';
?>
<div class="panel calendar-upcoming-snippet" id="my-module-snippet">

    <div class="panel-bath">
        <i class="fa fa-home"></i> <?= Yii::t('MyModule.base', '<strong>My</strong> snippet'); ?>
        <?= PanelMenu::widget(['id' => 'my-module-snippet', 'extraMenus' => $extraMenus]); ?>
    </div>

    <div class="panel-body" style="padding:0px;">
        <?php /* Put content */?>
    </div>

</div>
```

> Note: The snippet concept will be enhanced in a future version after HumHub 1.3 in order to provide a global and container settings view which can be used
to configurate all available snippets.

The following sections describe how to register your event listener for the different sidebars available.

### Dashboard Layout

Adding the following in your module's `config.php` file will enable the view of your snippet on your dashboard.

```php
namespace humhub\modules\yourmodule;

use humhub\modules\dashboard\widgets\Sidebar;

return [
    'id' => 'yourmodule',
    'class' => 'humhub\modules\yourmodule\Module',
    'namespace' => 'humhub\modules\yourmodule',
    'events' => [
        ['class' => Sidebar::class, 'event' => Sidebar::EVENT_INIT, 'callback' => ['humhub\modules\yourmodule\Module', 'onDashboardSidebarInit']],
  ],
];
```

### Space Layout

Adding the following in your module's `config.php` file will enable the view of your snippet in your Spaces.

```php
namespace humhub\modules\yourmodule;

use humhub\modules\space\widgets\Sidebar;

return [
    'id' => 'yourmodule',
    'class' => 'humhub\modules\yourmodule\Module',
    'namespace' => 'humhub\modules\yourmodule',
    'events' => [
        ['class' => Sidebar::class, 'event' => Sidebar::EVENT_INIT, 'callback' => ['humhub\modules\yourmodule\Events', 'onSpaceSidebarInit']],
  ],
];
```

### Directory Layout

Adding the following in your module's `config.php` file will enable the view of your snippet in your Directory.

```php
namespace humhub\modules\yourmodule;

use humhub\modules\directory\widgets\Sidebar;

return [
    'id' => 'yourmodule',
    'class' => 'humhub\modules\yourmodule\Module',
    'namespace' => 'humhub\modules\yourmodule',
    'events' => [
        ['class' => Sidebar::class, 'event' => Sidebar::EVENT_INIT, 'callback' => ['humhub\modules\yourmodule\Events', 'onDirectorySidebarInit']],
  ],
];
```
