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

namespace humhub\modules\space\commands;

use Yii;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;
use yii\helpers\Console;

/**
 * Console tools for manage spaces
 *
 * @package humhub.modules_core.space.console
 * @since 0.5
 */
class SpaceController extends \yii\console\Controller
{

    public function actionAssignAllMembers($spaceId)
    {
        $space = Space::findOne(['id' => $spaceId]);
        if ($space == null) {
            print "Error: Space not found! Check id!\n\n";
            return;
        }

        $countMembers = 0;
        $countAssigns = 0;

        $this->stdout("\nAdding Members:\n\n");

        foreach (User::find()->where(['status' => User::STATUS_ENABLED])->all() as $user) {
            if ($space->isMember($user->id)) {
                $countMembers++;
            } else {
                $this->stdout("\t" . $user->displayName . " added. \n", Console::FG_YELLOW);

                #Yii::app()->user->setId($user->id);

                Yii::$app->user->switchIdentity($user);
                $space->addMember($user->id);
                $countAssigns++;
            }
        }

        $this->stdout("\nAdded " . $countAssigns . " new members to space " . $space->name . "\n", Console::FG_GREEN);
    }

}
