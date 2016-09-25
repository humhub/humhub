<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\components;

use humhub\components\behaviors\AccessControl;

/**
 * Base controller for administration section
 *
 * @author luke
 */
class Controller extends \humhub\components\Controller
{

    public $subLayout = "@humhub/modules/admin/views/layouts/main";

    public function init()
    {
        $this->appendPageTitle(\Yii::t('AdminModule.base', 'Administration'));
        return parent::init();
    }

    public function behaviors()
    {
        return [
            'acl' => [
                'class' => AccessControl::className(),
                'adminOnly' => true
            ]
        ];
    }

}
