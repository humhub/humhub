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
        if (!$this->module->getIsEnabled()) {
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
        $this->forcePostRequest();

        $friend = User::findOne(['id' => Yii::$app->request->get('userId')]);

        if ($friend === null) {
            throw new HttpException(404, 'User not found!');
        }

        Friendship::add(Yii::$app->user->getIdentity(), $friend);

        return $this->redirect($friend->getUrl());
    }

    /**
     * Declines or Deletes Friendship
     * @throws HttpException
     */
    public function actionDelete()
    {
        $this->forcePostRequest();

        $friend = User::findOne(['id' => Yii::$app->request->get('userId')]);

        if ($friend === null) {
            throw new HttpException(404, 'User not found!');
        }

        Friendship::cancel(Yii::$app->user->getIdentity(), $friend);

        return $this->redirect($friend->getUrl());
    }

}
