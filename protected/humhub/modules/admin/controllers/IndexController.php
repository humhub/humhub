<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\modules\ui\menu\MenuLink;
use Yii;
use humhub\modules\admin\components\Controller;
use humhub\modules\admin\widgets\AdminMenu;
use yii\web\HttpException;

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
     * List all available user groups
     */
    public function actionIndex()
    {
        $adminMenu = new AdminMenu();

        /* @var $firstVisible MenuLink */
        $firstVisible = $adminMenu->getFirstEntry(MenuLink::class, true);

        if(!$firstVisible) {
            throw new HttpException(403);
        }

		return $this->redirect($firstVisible->getUrl());
    }

}
