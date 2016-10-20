<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\controllers;


use yii\data\ActiveDataProvider;

use humhub\modules\user\components\BaseAccountController;
use humhub\modules\friendship\models\Friendship;
use humhub\modules\friendship\models\SettingsForm;


/**
 * Membership Manage Controller
 *
 * @author luke
 */
class ManageController extends BaseAccountController
{

    public function actionIndex()
    {
        return $this->redirect(['list']);
    }

    public function actionList()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Friendship::getFriendsQuery($this->getUser()),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('list', [
                    'user' => $this->getUser(),
                    'dataProvider' => $dataProvider
        ]);
    }

    public function actionRequests()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Friendship::getReceivedRequestsQuery($this->getUser()),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('requests', [
                    'user' => $this->getUser(),
                    'dataProvider' => $dataProvider
        ]);
    }

    public function actionSentRequests()
    {
        $dataProvider = new ActiveDataProvider([
            'query' => Friendship::getSentRequestsQuery($this->getUser()),
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        return $this->render('sent-requests', [
                    'user' => $this->getUser(),
                    'dataProvider' => $dataProvider
        ]);
    }

}
