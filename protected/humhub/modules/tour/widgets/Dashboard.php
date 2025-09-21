<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

namespace humhub\modules\tour\widgets;

use humhub\components\SettingsManager;
use humhub\modules\tour\Module;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Widget;

/**
 * @author andystrobel
 */
class Dashboard extends Widget
{
    public function run()
    {
        /* @var Module $module */
        $module = Yii::$app->getModule('tour');
        $settingsManager = $module->settings->user();

        return $this->render('tourPanel', [
            'interface' => $settingsManager->get("interface"),
            'spaces' => $settingsManager->get("spaces"),
            'profile' => $settingsManager->get("profile"),
            'administration' => $settingsManager->get("administration"),
            'showWelcome' => $module->showWelcomeWindow(),
        ]);
    }

    public static function isVisible(?User $user = null): bool
    {
        /* @var SettingsManager $settings */
        $settings = Yii::$app->getModule('tour')->settings;

        return $settings->get('enable') == 1
            && $settings->user($user)->get('hideTourPanel') != 1;
    }
}
