<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\tour;

use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\tour\widgets\Dashboard;
use humhub\modules\user\models\User;
use Yii;

/**
 * Events provides callbacks for all defined module events.
 *
 * @since 1.15
 */
class Events
{
    const AUTO_START = 'autoStartTour';

    public static function onDashboardSidebarInit($event)
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        if (self::getModule()->settings->user()->get(self::AUTO_START)) {
            self::runAutoStartWelcomeTour();
        } elseif(self::shouldDisplayDashboardWidget()) {
            /* @var Sidebar $sidebar */
            $sidebar = $event->sender;
            $sidebar->addWidget(Dashboard::class, [], ['sortOrder' => 100]);
        }
    }

    public static function onUserBeforeLogin($event)
    {
        if ($event->identity instanceof User && self::shouldStartWelcomeTour($event->identity)) {
            self::getModule()->settings->user($event->identity)->set(self::AUTO_START, true);
        }
    }

    private static function getModule(): Module
    {
        return Yii::$app->getModule('tour');
    }

    private static function shouldDisplayDashboardWidget(?User $user = null): bool
    {
        $settings = self::getModule()->settings;
        return $settings->get('enable') == 1 &&
            $settings->user($user)->get('hideTourPanel') != 1;
    }

    private static function shouldStartWelcomeTour(?User $user = null): bool
    {
        return $user->last_login === null && // Force auto start only for new created user who is logged in first time after registration
            self::shouldDisplayDashboardWidget($user) && // Start it only when the dashboard sidebar widget is visible for the user
            !self::getModule()->showWelcomeWindow($user); // No need auto start because it will be done by dashboard widget
    }

    private static function runAutoStartWelcomeTour(): void
    {
        self::getModule()->settings->user()->delete(self::AUTO_START);
        Yii::$app->response->redirect(['/dashboard/dashboard', 'tour' => true]);
    }
}
