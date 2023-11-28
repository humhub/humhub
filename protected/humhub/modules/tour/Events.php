<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\tour;

use humhub\modules\dashboard\widgets\Sidebar;
use humhub\modules\tour\widgets\Dashboard;
use Yii;

/**
 * Events provides callbacks for all defined module events.
 *
 * @since 1.15
 */
class Events
{
    const AUTO_START_KEY = 'tour.autoStartWelcome';

    public static function onDashboardSidebarInit($event)
    {
        if (Yii::$app->user->isGuest) {
            return;
        }

        $settings = self::getModule()->settings;

        if ($settings->get('enable') == 1 && $settings->user()->get('hideTourPanel') != 1) {
            if (Yii::$app->session->get(self::AUTO_START_KEY)) {
                self::runAutoStartWelcomeTour();
            } else {
                /* @var Sidebar $sidebar */
                $sidebar = $event->sender;
                $sidebar->addWidget(Dashboard::class, [], ['sortOrder' => 100]);
            }
        }
    }

    public static function onAfterLogin()
    {
        if (self::shouldStartWelcomeTour()) {
            Yii::$app->session->set(self::AUTO_START_KEY, true);
        }
    }

    private static function getModule(): Module
    {
        return Yii::$app->getModule('tour');
    }

    private static function shouldStartWelcomeTour(): bool
    {
        if (Yii::$app->user->isGuest) {
            // Don't start it for guest
            return false;
        }

        if (self::getModule()->showWelcomeWindow()) {
            // No need auto start because it will be done by dashboard widget
            return false;
        }

        $user = Yii::$app->user->identity;

        // Force auto start only for new created user
        return $user->updated_by === null && $user->created_at === $user->updated_at;
    }

    private static function runAutoStartWelcomeTour(): void
    {
        Yii::$app->session->remove(self::AUTO_START_KEY);
        Yii::$app->user->identity->updateAttributes(['updated_by' => Yii::$app->user->id]);
        Yii::$app->response->redirect(['/dashboard/dashboard', 'tour' => true]);
    }
}
