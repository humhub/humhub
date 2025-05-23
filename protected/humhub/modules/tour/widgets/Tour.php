<?php

namespace humhub\modules\tour\widgets;

use humhub\components\SettingsManager;
use humhub\components\Widget;
use humhub\modules\tour\assets\TourAsset;
use humhub\modules\tour\Module;
use humhub\modules\tour\TourConfig;
use humhub\modules\tour\widgets\Dashboard as DashboardWidget;
use humhub\modules\user\models\User;
use Yii;

/**
 * Will show the introduction tour
 *
 * @package humhub.modules_core.tour.widgets
 * @since 0.5
 * @author andystrobel
 */
class Tour extends Widget
{

    /**
     * Executes the widgets
     */
    public function run()
    {
        if (Yii::$app->user->isGuest) {
            return '';
        }

        // Active Tour flag is not set and auto start is not enabled
        if (!Yii::$app->request->get('tour') && !self::isEnabledAutoStart()) {
            return '';
        }

        // Tour only possible when we are in a module
        if (Yii::$app->controller->module === null) {
            return '';
        }

        // Check if Tour is activated by admin and users
        if (!DashboardWidget::isVisible()) {
            return '';
        }

        $config = TourConfig::getCurrent();

        if (!$config) {
            return '';
        }

        self::disableAutoStart(TourConfig::getTourId($config));

        TourAsset::register($this->view);

        return $this->render('tour', ['config' => $config]);
    }

    private static function getSettings(): SettingsManager
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('tour');
        return $module->settings;
    }

    public static function isEnabledAutoStart(?string $tourId = null, ?User $user = null): bool
    {
        if ($tourId === null) {
            $config = TourConfig::getCurrent();
            if (!$config) {
                return false;
            }
            $tourId = TourConfig::getTourId($config);
        }

        return (bool)self::getSettings()->user($user)->get('autoStartTour.' . $tourId, false);
    }

    public static function enableAutoStart(string $tourId, ?User $user = null)
    {
        self::getSettings()->user($user)->set('autoStartTour.' . $tourId, true);
    }

    public static function disableAutoStart(string $tourId, ?User $user = null)
    {
        if (self::isEnabledAutoStart($tourId)) {
            self::getSettings()->user($user)->delete('autoStartTour.' . $tourId);
        }
    }
}
