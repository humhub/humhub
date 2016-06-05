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

use Yii;

/**
 * @author andystrobel
 */
class Dashboard extends \yii\base\Widget
{

    public function run()
    {
        $settingsManager = Yii::$app->getModule('tour')->settings->user();

        return $this->render('tourPanel', [
                    'interface' => $settingsManager->get("interface"),
                    'spaces' => $settingsManager->get("spaces"),
                    'profile' => $settingsManager->get("profile"),
                    'administration' => $settingsManager->get("administration"),
                    'showWelcome' => (Yii::$app->user->id == 1 && Yii::$app->getModule('installer')->settings->get('sampleData') != 1 && $settingsManager->get('welcome') != 1)
        ]);
    }

}
