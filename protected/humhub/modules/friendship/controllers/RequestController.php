<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\controllers;

use Yii;
use humhub\modules\user\models\User;
use humhub\components\Controller;
use humhub\modules\friendship\models\Friendship;

/**
 * Membership Handling Controller
 *
 * @author luke
 */
class RequestController extends Controller
{

    /**
     * Adds or Approves Friendship Request
     */
    public function actionAdd()
    {
        $this->forcePostRequest();

        $friend = User::findOne(['id' => Yii::$app->request->get('userId')]);

        if ($friend === null) {
            throw new \yii\web\HttpException(404, 'User not found!');
        }

        Friendship::add(Yii::$app->user->getIdentity(), $friend);

        return $this->redirect($friend->getUrl());
    }

    /**
     * Declines or Deletes Friendship
     */
    public function actionDelete()
    {
        $this->forcePostRequest();

        $friend = User::findOne(['id' => Yii::$app->request->get('userId')]);

        if ($friend === null) {
            throw new \yii\web\HttpException(404, 'User not found!');
        }

        Friendship::cancel(Yii::$app->user->getIdentity(), $friend);

        return $this->redirect($friend->getUrl());
    }

}
