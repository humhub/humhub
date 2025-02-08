<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification;

/**
 * Notification Module
 */
class Module extends \humhub\components\Module
{
    /**
     * @var int Delete read notifications after 2 months(by default)
     */
    public $deleteSeenNotificationsMonths = 2;

    /**
     * @var int Delete unread notifications after 3 months(by default)
     */
    public $deleteUnseenNotificationsMonths = 3;

    /**
     * @var bool Disable "New Content" Notifications for default Spaces the User is not a member of and not following
     * In the User Notification Settings, "Receive 'New Content' Notifications for the following spaces"
     * will not have the Spaces that are configured in the Admin "Settings and Configuration" page
     * that the user is not a member of and does not follow.
     */
    public bool $disableNewContentNotificationSpacesToNonMemberFollowing = false;
}
