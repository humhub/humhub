<?php

namespace humhub\modules\tour\widgets;

use humhub\components\SettingsManager;
use humhub\components\Widget;
use humhub\modules\admin\controllers\ModuleController;
use humhub\modules\dashboard\controllers\DashboardController;
use humhub\modules\space\controllers\SpaceController;
use humhub\modules\tour\assets\TourAsset;
use humhub\modules\tour\models\TourParams;
use humhub\modules\tour\Module;
use humhub\modules\tour\widgets\Dashboard as DashboardWidget;
use humhub\modules\user\controllers\ProfileController;
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
    private static function getTypes(): array
    {
        return [
            'interface' => ['view' => 'guide_interface', 'controller' => DashboardController::class],
            'space' => ['view' => 'guide_spaces', 'controller' => SpaceController::class],
            'user' => ['view' => 'guide_profile', 'controller' => ProfileController::class],
            'admin' => ['view' => 'guide_administration', 'controller' => ModuleController::class],
        ];
    }

    /**
     * Executes the widgets
     */
    public function run()
    {
        if (Yii::$app->user->isGuest) {
            return '';
        }

        // Active tour flag is not set and auto start is not enabled
        if (!Yii::$app->request->get('tour') && !self::isEnabledAutoStart()) {
            return '';
        }

        // Tour only possible when we are in a module
        if (Yii::$app->controller->module === null) {
            return '';
        }

        // Check if tour is activated by admin and users
        if (!DashboardWidget::isVisible()) {
            return '';
        }

        $params = TourParams::getCurrent();

        if (!$params) {
            return '';
        }

        self::disableAutoStart($params[TourParams::KEY_PAGE]);

        TourAsset::register($this->view);

        return $this->render('tour', ['params' => $params]);
    }

    private static function getSettings(): SettingsManager
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('tour');
        return $module->settings;
    }

    public static function isEnabledAutoStart(?string $page = null, ?User $user = null): bool
    {
        if ($page === null) {
            $params = TourParams::getCurrent();
            if (!$params) {
                return false;
            }
            $page = $params[TourParams::KEY_PAGE];
        }

        return (bool)self::getSettings()->user($user)->get('autoStartTour.' . $page, false);
    }

    public static function enableAutoStart(string $page, ?User $user = null)
    {
        self::getSettings()->user($user)->set('autoStartTour.' . $page, true);
    }

    public static function disableAutoStart(string $page, ?User $user = null)
    {
        if (self::isEnabledAutoStart($page)) {
            self::getSettings()->user($user)->delete('autoStartTour.' . $page);
        }
    }
}
