<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\dashboard;

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

}
