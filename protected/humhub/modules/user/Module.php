<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user;

use Yii;

/**
 * User Module
 */
class Module extends \humhub\components\Module
{

    /**
     * @inheritdoc
     */
    public $controllerNamespace = 'humhub\modules\user\controllers';

    /**
     * @var boolean option to translate all invite mails except self invites to the default language (true) or user language (false) 
     */
    public $sendInviteMailsInGlobalLanguage = true;

    /**
     * @var boolean default state of remember me checkbox on login page
     */
    public $loginRememberMeDefault = true;

    /**
     * @var int number of seconds that the user can remain in logged-in status if remember me is clicked on login
     */
    public $loginRememberMeDuration = 2592000;

    /**
     * @var string redirect url after logout (if not set, home url will be used)
     */
    public $logoutUrl = null;

    /**
     * @var string the default route for user profiles
     */
    public $profileDefaultRoute = null;

    /**
     * @var int the default pagination size of the user list lightbox
     * @see widgets\UserListBox
     */
    public $userListPaginationSize = 8;

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer instanceof models\User) {
            return [
                new permissions\ViewAboutPage(),
            ];
        }

        return [];
    }

    public function getName()
    {
        return Yii::t('UserModule.base', 'User');
    }

    /**
     * @inheritdoc
     */
    public function getNotifications()
    {
        return [
            'humhub\modules\user\notifications\Followed',
            'humhub\modules\user\notifications\Mentioned'
        ];
    }

}
