<?php

namespace humhub\modules\tour\widgets;

use humhub\components\SettingsManager;
use humhub\components\Widget;
use humhub\modules\admin\controllers\ModuleController;
use humhub\modules\dashboard\controllers\DashboardController;
use humhub\modules\space\controllers\SpaceController;
use humhub\modules\tour\assets\TourAsset;
use humhub\modules\tour\widgets\Dashboard as DashboardWidget;
use humhub\modules\user\controllers\ProfileController;
use humhub\modules\user\models\User;
use Yii;
use yii\web\View;

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
            'dashboard' => ['view' => 'guide_interface', 'controller' => DashboardController::class],
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

        $type = self::getCurrentType();

        if ($type === null) {
            return '';
        }

        self::disableAutoStart($type['type']);

        TourAsset::register($this->view);

        return $this->render($type['view']);
    }

    /**
     * @deprecated since 1.3.13
     */
    public function loadResources(View $view)
    {
        // Dummy for old template version
    }

    private static function getSettings(): SettingsManager
    {
        return Yii::$app->getModule('tour')->settings;
    }

    public static function isEnabledAutoStart(?string $type = null, ?User $user = null): bool
    {
        if ($type === null) {
            $type = self::getCurrentType();
            if ($type === null) {
                return false;
            }
            $type = $type['type'];
        }

        return (bool)self::getSettings()->user($user)->get('autoStartTour.' . $type, false);
    }

    public static function enableAutoStart(string $type, ?User $user = null)
    {
        self::getSettings()->user($user)->set('autoStartTour.' . $type, true);
    }

    public static function disableAutoStart(string $type, ?User $user = null)
    {
        if (self::isEnabledAutoStart($type)) {
            self::getSettings()->user($user)->delete('autoStartTour.' . $type);
        }
    }

    private static function getCurrentType(): ?array
    {
        foreach (self::getTypes() as $type => $tour) {
            if (Yii::$app->controller instanceof $tour['controller']) {
                $tour['type'] = $type;
                return $tour;
            }
        }

        return null;
    }
}
