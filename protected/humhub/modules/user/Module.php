<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\like\models\Like;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Group;
use humhub\modules\user\permissions\CanMention;
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
     * @var string Regular expression to check username characters
     * @note Example to allow more characters: /^[\p{L}\d_\-@#$%^&*\(\)\[\]\{\}+=<>:;,.?!|~"\'\\\\]+$/iu
     * @since 1.8
     */
    public $validUsernameRegexp = '/^[\p{L}\d_\-@\.]+$/iu';

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
     * @var bool disable profile stream
     * @since 1.6
     */
    public $profileDisableStream = false;

    /**
     * Account login blocking times after attempted incorrect logins.
     * Format: Number of tries => Time delay in seconds.
     * @since 1.8
     * @var int[]
     */
    public $failedLoginDelayTimes = [
        // No delay for less than 3 failed attempts
        2 => 10,
        6 => 20,
    ];


    /**
     * @var array Forbidden names to register
     * @since 1.11
     */
    public $forbiddenUsernames = [];

    /**
     * @var string include user's email address in searches
     * @since 1.11
     */
    public $includeEmailInSearch = true;

    /**
     * @inheritdoc
     */
    public function getPermissions($contentContainer = null)
    {
        if ($contentContainer instanceof models\User) {
            $permissions = [
                new permissions\ViewAboutPage(),
            ];

            if(Yii::$app->getModule('friendship')->getIsEnabled()) {
                $permissions[] = new permissions\CanMention();
            }

            return $permissions;
        } elseif ($contentContainer instanceof Space) {
            return [];
        }

        return [
            new permissions\PeopleAccess(),
        ];
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
            $this->passwordStrength = $this->getDefaultPasswordStrength();
        }
        return $this->passwordStrength;
    }

    /**
     * @return array the default rules for password validation
     * @since 1.6.5
     */
    private function getDefaultPasswordStrength()
    {
        return [
            '/^.{5,255}$/' => Yii::t('UserModule.base', 'Password needs to be at least {chars} characters long.', ['chars' => 5]),
        ];
    }

    /**
     * @inheritdoc
     */
    public function isCustomPasswordStrength()
    {
        return $this->getDefaultPasswordStrength() !== $this->getPasswordStrength();
    }

    /**
     * Get default group
     * @return Group
     */
    public function getDefaultGroup()
    {
        return Group::findOne(['is_default_group' => 1, 'is_admin_group' => 0]);
    }

    /**
     * Get default group id
     * @return integer|null
     */
    public function getDefaultGroupId()
    {
        $defaultGroup = $this->getDefaultGroup();
        return $defaultGroup ? $defaultGroup->id : null;
    }

    /**
     * Set default group
     * @param int
     */
    public function setDefaultGroup($id)
    {
        $group = Group::findOne(['id' => $id]);
        if ($group && !$group->is_admin_group && !$group->is_default_group) {
            $group->is_default_group = 1;
            $group->save();
        }
    }

    /**
     * Check the blocking users is allowed
     *
     * @return bool
     */
    public function allowBlockUsers(): bool
    {
        return (bool) $this->settings->get('auth.blockUsers', true);
    }

    /**
     * Checks if user can be mentioned
     *
     * @param ContentActiveRecord $object
     * @return boolean can like
     */
    public function canMention($object)
    {
//        $content = $object->content;

//        if(!isset($content->container)) {
//            return false;
//        }

        if ($object->permissionManager->can(CanMention::class)) {
            return true;
        }

        return false;
    }
}
