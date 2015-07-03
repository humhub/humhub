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

namespace humhub\modules\space\controllers;

use Yii;
use \humhub\components\Controller;
use \yii\helpers\Url;
use \yii\web\HttpException;
use \humhub\modules\user\models\User;
use humhub\models\Setting;
use humhub\modules\space\models\Membership;

/**
 * SpaceController is the main controller for spaces.
 *
 * It show the space itself and handles all related tasks like following or
 * memberships.
 *
 * @author Luke
 * @package humhub.modules_core.space.controllers
 * @since 0.5
 */
class SpaceController extends \humhub\modules\content\components\ContentContainerController
{

    public function actions()
    {
        return array(
            'stream' => array(
                'class' => \humhub\modules\content\components\actions\ContentContainerStream::className(),
                'mode' => \humhub\modules\content\components\actions\ContentContainerStream::MODE_NORMAL,
                'contentContainer' => $this->getSpace()
            ),
        );
    }

    /**
     * Generic Start Action for Profile
     */
    public function actionIndex()
    {
        return $this->render('index', ['space' => $this->contentContainer]);
    }

    /**
     * Follows a Space
     */
    public function actionFollow()
    {
        $this->forcePostRequest();
        $space = $this->getSpace();
        if (!$space->isMember()) {
            $space->follow();
        }

        return $this->redirect($space->getUrl());
    }

    /**
     * Unfollows a Space
     */
    public function actionUnfollow()
    {
        $this->forcePostRequest();
        $space = $this->getSpace();
        $space->unfollow();

        return $this->redirect($space->getUrl());
    }

}

?>
