<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\directory;

use humhub\modules\ui\menu\MenuLink;
use Yii;
use humhub\modules\user\models\Group;
use humhub\modules\directory\permissions\AccessDirectory;

/**
 * Directory Base Module
 *
 * The directory module adds a menu item "Directory" to the top navigation
 * with some lists about spaces, users or group inside the application.
 *
 * @package humhub.modules_core.directory
 * @since 0.5
 * @deprecated since 1.9 but it can be activated temporary by console command `php yii directory/activate`
 */
class Module extends \humhub\components\Module
{

    /**
     * @inheritdoc
     */
    public $isCoreModule = true;

    /**
     * @var string sort field (e.g. lastname) of member list
     */
    public $memberListSortField = 'profile.lastname';

    /**
     * @var int default page size for directory pages
     */
    public $pageSize = 25;

    /**
     * @var bool defines if the directory is active, if not the directory is not visible and can't be accessed
     */
    public $active = false;

    /**
     * @var bool defines if the directory is available for guest users, this flag will only have effect if guest access is allowed and the module is active
     */
    public $guestAccess = true;

    /**
     * @var bool show menu entry for user profile posts on directory
     */
    public $showUserProfilePosts = true;

    /**
     * @inerhitdoc
     */
    public function init()
    {
        parent::init();

        $this->active = $this->settings->get('isActive', false);
    }

    /**
     * @return bool checks if the current user can access the directory
     */
    public function canAccess()
    {
        if(!$this->active) {
            return false;
        }

        if(Yii::$app->user->isGuest) {
            return $this->guestAccess;
        }

        return Yii::$app->user->can(AccessDirectory::class);
    }

    /**
     * On build of the TopMenu, check if module is enabled
     * When enabled add a menu item
     *
     * @param type $event
     */
    public static function onTopMenuInit($event)
    {
        /** @var static $module */
        $module = Yii::$app->getModule('directory');

        if($module->canAccess()) {
            $event->sender->addEntry(new MenuLink([
                'id' => 'directory',
                'icon' => 'directory',
                'label' => Yii::t('DirectoryModule.base', 'Directory'),
                'url' => ['/directory/directory'],
                'sortOrder' => 400,
                'isActive' =>  MenuLink::isActiveState('directory'),
            ]));
        }
    }

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($this->active && !$contentContainer) {
            return [
                new AccessDirectory(),
            ];
        }

        return [];
    }

    /**
     * Show groups in directory
     *
     * @return boolean
     */
    public function isGroupListingEnabled()
    {
        return (Group::find()->where(['show_at_directory' => 1])->count() != 0);
    }

}
