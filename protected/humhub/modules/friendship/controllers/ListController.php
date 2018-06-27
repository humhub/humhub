<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\friendship\controllers;

use Yii;
use yii\web\HttpException;
use humhub\modules\user\models\User;
use humhub\modules\friendship\models\Friendship;
use humhub\components\Controller;
use humhub\modules\user\widgets\UserListBox;

/**
 * ListController
 *
 * @since 1.1
 * @author luke
 */
class ListController extends Controller
{

    /**
     * Returns an list of all friends of a user
     *
     * @throws HttpException
     * @throws \Exception
     */
    public function actionPopup()
    {
        $user = User::findOne(['id' => Yii::$app->request->get('userId')]);
        if ($user === null) {
            throw new HttpException(404, 'Could not find user!');
        }

        $query = Friendship::getFriendsQuery($user);

        $title = '<strong>' . Yii::t('FriendshipModule.base', 'Friends') . '</strong>';

        return $this->renderAjaxContent(UserListBox::widget(['query' => $query, 'title' => $title]));
    }

}
