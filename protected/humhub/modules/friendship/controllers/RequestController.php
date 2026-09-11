<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\controllers;

use humhub\components\Controller;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\friendship\Module;
use humhub\modules\user\models\User;
use Yii;
use yii\web\HttpException;
use yii\web\Response;

/**
 * Membership Handling Controller
 *
 * @property Module $module
 * @author luke
 */
class RequestController extends Controller
{
    /**
     * @inheritdoc
     * @throws HttpException
     */
    public function beforeAction($action)
    {
        if (!$this->module->isFriendshipEnabled()) {
            throw new HttpException(404, 'Friendship system is not enabled!');
        }

        return parent::beforeAction($action);
    }


    /**
     * Adds or Approves Friendship Request
     * @throws HttpException
     */
    public function actionAdd()
    {
        $friend = $this->getFriendUser();

        Friendship::add(Yii::$app->user->getIdentity(), $friend);

        return $this->getActionResult($friend);
    }

    /**
     * Declines or Deletes Friendship
     * @throws HttpException
     */
    public function actionDelete()
    {
        $friend = $this->getFriendUser();

        Friendship::cancel(Yii::$app->user->getIdentity(), $friend);

        return $this->getActionResult($friend);
    }

    /**
     * Get friend User from request
     *
     * @return User
     * @throws HttpException
     */
    protected function getFriendUser(): User
    {
        $this->forcePostRequest();

        $friend = User::findOne(['id' => Yii::$app->request->get('userId')]);

        if ($friend === null) {
            throw new HttpException(404, 'User not found!');
        }

        return $friend;
    }

    /**
     * Result of the friendship actions: back where the request came from.
     *
     * Until 1.20 an AJAX request was answered with a re-rendered friendship button instead —
     * the reason its presentation options had to travel to the client and back. The button is
     * a Vue island now and updates itself from what the API answers, so nothing but the
     * redirect is left (see `friendship\widgets\FriendshipButton`).
     *
     * @param User $user
     * @return Response
     */
    protected function getActionResult(User $user)
    {
        return $this->redirect($this->request->getReferrer());
    }

}
