<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dashboard;

use humhub\modules\dashboard\stream\filters\DashboardGuestStreamFilter;
use humhub\modules\dashboard\stream\filters\DashboardMemberStreamFilter;
use Yii;

/**
 * Dashboard Module
 *
 * @author Luke
 */
class Module extends \humhub\components\Module
{

    /**
     * Possible options to include profile posts into the dashboard stream
     *
     * Default/Null: Default, only include profile posts when user is followed
     * Always: Always include all user profile posts into dashboards
     * Admin Only: For admin users, always include all profile posts (without following)
     */
    const STREAM_AUTO_INCLUDE_PROFILE_POSTS_ALWAYS = 'all';
    const STREAM_AUTO_INCLUDE_PROFILE_POSTS_ADMIN_ONLY = 'admin';

    /**
     * @inheritdocs
     */
    public $controllerNamespace = 'humhub\modules\dashboard\controllers';

    /**
     * @since 1.2.4
     * @var string profile
     */
    public $autoIncludeProfilePosts = null;


    /**
     * @since 1.3.14
     * @var boolean hides the activities sidebar widget
     */
    public $hideActivitySidebarWidget = false;

    /**
     * Dashboard stream query filter class used for guest users
     * @var string
     * @since 1.8
     */
    public $guestFilterClass = DashboardGuestStreamFilter::class;

    /**
     * Dashboard stream query filter class used for members of the network
     * @var string
     * @since 1.8
     */
    public $memberFilterClass = DashboardMemberStreamFilter::class;

    /**
     * @return static
     */
    public static function getModuleInstance()
    {
        /* @var $module static */
        $module = Yii::$app->getModule('dashboard');
        return $module;
    }

}
