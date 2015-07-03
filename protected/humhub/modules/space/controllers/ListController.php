<?php

namespace humhub\modules\space\controllers;

use Yii;
use \humhub\components\Controller;
use \yii\helpers\Url;
use \yii\web\HttpException;
use \humhub\modules\user\models\User;
use \humhub\models\Setting;
use \humhub\modules\space\models\Membership;

/**
 * ListController
 *
 * @author Luke
 * @package humhub.modules_core.space.controllers
 * @since 0.5
 */
class ListController extends Controller
{

    public function actionIndex()
    {
        $query = Membership::find();

        if (Setting::Get('spaceOrder', 'space') == 0) {
            $query->orderBy('name ASC');
        } else {
            $query->orderBy('last_visit DESC');
        }

        $query->joinWith('space');
        $query->where(['space_membership.user_id'=>Yii::$app->user->id, 'space_membership.status'=>  Membership::STATUS_MEMBER]);

        return $this->renderAjax('index', ['memberships' => $query->all()]);
    }

}
