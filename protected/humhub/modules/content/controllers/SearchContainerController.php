<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\controllers;

use humhub\modules\content\Module;
use humhub\modules\space\controllers\BrowseController;
use humhub\modules\user\widgets\UserPicker;
use Yii;

/**
 * @property Module $module
 */
class SearchContainerController extends BrowseController
{
    public function actionJson()
    {
        return $this->actionSearchJson();
    }

    protected function prepareResult($spaces)
    {
        $json = parent::prepareResult($spaces);
        if (Yii::$app->user->identity) {
            $currentUser = UserPicker::createJSONUserInfo(Yii::$app->user->identity);
            array_unshift($json, $currentUser);
        }
        return $json;
    }
}
