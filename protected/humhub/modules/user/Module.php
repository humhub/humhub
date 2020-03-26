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
     * @var boolean allow admin users to modify user profile image and banner
     * @since 1.2
     * @see widgets\ProfileHeader
     */
    public $adminCanChangeUserProfileImages = false;

    /**
     * @var int maximum username length
     * @since 1.3
     */
    public $maximumUsernameLength = 50;

    /**
     * @var int minimum username length
     * @since 1.2
     */
    public $minimumUsernameLength = 4;

    /**
     * @var callable a callback that returns the user displayName
     * @since 1.2
     */
    public $displayNameCallback = null;

    /**
     * @var callable a callback that returns the user displayName sub text
     */
    public $displayNameSubCallback = null;

    /**
     * @var boolean defines if the user following is disabled or not.
     * @since 1.2
     */
    public $disableFollow = false;

    /**
     * @var boolean defines mark user e-mail field as required
     * @since 1.2.2
     */
    public $emailRequired = true;

    /**
     * @var array profile field names to keep after user soft deletion
     * @since 1.3
     */
    public $softDeleteKeepProfileFields = ['firstname', 'lastname'];

    /**
     * @var bool include user contents on profile stream
     * @since 1.5
     */
    public $includeAllUserContentsOnProfile = true;

    /**
     * @var array defines empty additional rules for password validation
     */
    public $passwordStrength = [];

    /**
     * @var array defines default additional rules for password validation
     */
    private $defaultPasswordStrength = [
        '/^.{5,255}$/' => 'Password needs to be at least 8 characters long.',
    ];

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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function getPasswordStrength()
    {
        if (empty($this->passwordStrength)) {
            $this->passwordStrength = $this->defaultPasswordStrength;
        }
        return $this->passwordStrength;
    }

    /**
     * @inheritdoc
     */
    public function isCustomPasswordStrength()
    {
        return $this->defaultPasswordStrength !== $this->getPasswordStrength();
    }
}
