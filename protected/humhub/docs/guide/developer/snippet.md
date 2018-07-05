Sidebars and Snippets
======================

Snippets are self contained panels which can be added to the sidebar, for example the space, directory, or dashboard layout.


## Adding content to the sidebar

You can use the [Yii2 View Blocks](https://www.yiiframework.com/doc/guide/2.0/en/structure-views#using-blocks) feature to move content from your view file into the sidebar.

Example:

```php
<?php $this->beginBlock('sidebar'); ?>
Your sidebar content
<?php $this->endBlock(); ?>
```

See also: [[humhub\modules\ui\view\components\View|getSidebar]]


## Event Handlers

### Dashboard Layout
Adding the following in your module's `config.php` file will enable the view of your snippet on your dashboard.

```php
namespace humhub\modules\yourmodule;

use humhub\widgets\BaseMenu;

return [
    'id' => 'yourmodule',
    'class' => 'humhub\modules\yourmodule\Module',
    'namespace' => 'humhub\modules\yourmodule',
    'events' => [
        ['class' => humhub\modules\dashboard\widgets\Sidebar::className(), 'event' => BaseMenu::EVENT_INIT, 'callback' => ['humhub\modules\yourmodule\Module', 'onDashboardSidebarInit']],
  ],
];
```

### Space Layout
Adding the following in your module's `config.php` file will enable the view of your snippet in your Spaces.

```php
namespace humhub\modules\yourmodule;

use humhub\widgets\BaseMenu;

return [
    'id' => 'yourmodule',
    'class' => 'humhub\modules\yourmodule\Module',
    'namespace' => 'humhub\modules\yourmodule',
    'events' => [
        ['class' => humhub\modules\space\widgets\Sidebar::className(), 'event' => BaseMenu::EVENT_INIT, 'callback' => ['humhub\modules\yourmodule\Events', 'onSpaceSidebarInit']],
  ],
];
```

### Directory Layout
Adding the following in your module's `config.php` file will enable the view of your snippet in your Directory.

```php
namespace humhub\modules\yourmodule;

use humhub\widgets\BaseMenu;

return [
    'id' => 'yourmodule',
    'class' => 'humhub\modules\yourmodule\Module',
    'namespace' => 'humhub\modules\yourmodule',
    'events' => [
        ['class' => humhub\modules\directory\widgets\Sidebar::className(), 'event' => BaseMenu::EVENT_INIT, 'callback' => ['humhub\modules\yourmodule\Events', 'onDirectorySidebarInit']],
  ],
];
```
