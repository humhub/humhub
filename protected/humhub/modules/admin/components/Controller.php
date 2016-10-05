<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\components;

use Yii;
use humhub\components\behaviors\AccessControl;

/**
 * Base controller for administration section
 *
 * @author luke
 */
class Controller extends \humhub\components\Controller
{

    public $subLayout = "@humhub/modules/admin/views/layouts/main";
    public $adminOnly = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->appendPageTitle(\Yii::t('AdminModule.base', 'Administration'));
        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        // Workaround for module configuration actions @see getAccessRules()
        if (Yii::$app->controller->module->id != 'admin') {
            $this->adminOnly = false;
        }

        return [
            'acl' => [
                'class' => AccessControl::className(),
                'adminOnly' => $this->adminOnly,
                'rules' => static::getAcessRules()
            ]
        ];
    }

    public static function getAcessRules()
    {
        // Workaround for module configuration actions
        if (Yii::$app->controller->module->id != 'admin') {
            return [
                ['permissions' => \humhub\modules\admin\permissions\ManageModules::className()]
            ];
        }

        return [];
    }

}
