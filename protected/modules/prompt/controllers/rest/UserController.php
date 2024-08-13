<?php

namespace app\humhub\modules\prompt\controllers\rest;

use humhub\modules\rest\components\BaseController;

class UserController extends BaseController
{
    public function actionAdd($messageId, $userId)
    {
        return ['message' => 'User added to message!'];
    }
}