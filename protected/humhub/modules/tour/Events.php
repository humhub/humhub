<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\tour;

use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\tour\widgets\Dashboard as DashboardWidget;
use humhub\modules\tour\widgets\Tour;
use humhub\modules\user\models\User;
use Yii;

/**
 * Events provides callbacks for all defined module events.
 *
 * @since 1.15
 */
class Events
{
    public static function onDashboardSidebarInit($event)
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        if (DashboardWidget::isVisible()) {
            /* @var Sidebar $sidebar */
            $sidebar = $event->sender;
            $sidebar->addWidget(DashboardWidget::class, [], ['sortOrder' => 100]);
        }
    }

    public static function onUserBeforeLogin($event)
    {
        if ($event->identity instanceof User && self::shouldStartWelcomeTour($event->identity)) {
            Tour::enableAutoStart('dashboard', $event->identity);
        }
    }

    private static function shouldStartWelcomeTour(?User $user = null): bool
    {
        return $user->last_login === null // Force auto start only for new created user who is logged in first time after registration
            && DashboardWidget::isVisible($user) // Start it only when the dashboard sidebar widget is visible for the user
            && !Yii::$app->getModule('tour')->showWelcomeWindow($user); // No need auto start because it will be done by dashboard widget
    }
}
