<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use Yii;
use humhub\modules\admin\components\Controller;

/**
 * IndexController is the admin section start point.
 *
 * @since 0.5
 */
class IndexController extends Controller
{

    /**
     * @inheritdoc
     */
    public $adminOnly = false;

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['permissions' => Yii::$app->getModule('admin')->getPermissions()]
        ];
    }

    /**
     * List all available user groups
     */
    public function actionIndex()
    {
        $adminMenu = new \humhub\modules\admin\widgets\AdminMenu();

		return $this->redirect($adminMenu->items[0]['url']);
    }

}
