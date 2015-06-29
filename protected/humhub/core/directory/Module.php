<?php

namespace humhub\core\directory;

use Yii;

/**
 * Directory Base Module
 *
 * The directory module adds a menu item "Directory" to the top navigation
 * with some lists about spaces, users or group inside the application.
 *
 * @package humhub.modules_core.directory
 * @since 0.5
 */
class Module extends \yii\base\Module
{

    public $isCoreModule = true;

    /**
     * On build of the TopMenu, check if module is enabled
     * When enabled add a menu item
     *
     * @param type $event
     */
    public static function onTopMenuInit($event)
    {
        $event->sender->addItem(array(
            'label' => Yii::t('DirectoryModule.base', 'Directory'),
            'id' => 'directory',
            'icon' => '<i class="fa fa-book"></i>',
            'url' => \yii\helpers\Url::to(['/directory/directory']),
            'sortOrder' => 400,
            'isActive' => (Yii::$app->controller->module && Yii::$app->controller->module->id == 'directory'),
        ));
    }

}
