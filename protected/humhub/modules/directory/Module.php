<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory;

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
class Module extends \humhub\components\Module
{

    /**
     * @inheritdoc
     */
    public $isCoreModule = true;

    /**
     * @var string sort field (e.g. lastname) of member list (leave empty to sort by auto sort search)
     */
    public $memberListSortField = "";

    /**
     * @var int default page size for directory pages
     */
    public $pageSize = 25;

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

    /**
     * Show groups in directory
     * 
     * @return boolean
     */
    public function isGroupListingEnabled()
    {
        return (\humhub\modules\user\models\Group::find()->where(['show_at_directory' => 1])->count() > 1);
    }

}
