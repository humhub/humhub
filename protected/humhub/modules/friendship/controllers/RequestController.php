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
        $friend = User::findOne(['id' => Yii::$app->request->get('userId')]);

        $friendship = new Friendship();
        $friendship->user_id = Yii::$app->user->id;
        $friendship->friend_user_id = $friend->id;
        $friendship->save();

        return $this->redirect($friend->getUrl());
    }

    /**
     * Declines or Deletes Friendship
     */
    public function actionDelete()
    {
        $friend = User::findOne(['id' => Yii::$app->request->get('userId')]);

        // Delete my entry 
        $myFriendship = Friendship::findOne(['user_id' => Yii::$app->user->id, 'friend_user_id' => $friend->id]);
        if ($myFriendship !== null) {
            $myFriendship->delete();
        }

        // Delete friends entry
        $friendsFriendship = Friendship::findOne(['user_id' => $friend->id, 'friend_user_id' => Yii::$app->user->id]);
        if ($friendsFriendship !== null) {
            $friendsFriendship->delete();
        }

        return $this->redirect($friend->getUrl());
    }

}
