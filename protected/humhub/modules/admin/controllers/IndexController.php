<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\controllers;

use humhub\modules\admin\components\Controller;

/**
 * IndexController is the admin section start point.
 * 
 * @since 0.5
 */
class IndexController extends Controller
{

    /**
     * List all available user groups
     */
    public function actionIndex()
    {
        return $this->redirect(['/admin/user']);
    }

}
