<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\share\controllers;

use Yii;
use yii\web\HttpException;


/**
 * TourController
 *
 * @author andystrobel
 */
class ShareController extends \humhub\components\Controller
{

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
            ]
        ];
    }

    /*
   * Update user settings for hiding tour panel on dashboard
   */

    public function actionHidePanel()
    {
        // set tour status to seen for current user
        return Yii::$app->user->getIdentity()->setSetting('hideSharePanel', 1, "share");
    }

}
