<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
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

    /**
     * @inheritdoc
     */
    public $subLayout = "@humhub/modules/admin/views/layouts/main";

    /**
     * @var boolean if true only allows access for system admins else the access is restricted by getAccessRules()
     */
    public $adminOnly = true;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->appendPageTitle(Yii::t('AdminModule.base', 'Administration'));

		parent::init();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        // Workaround for module configuration actions @see getAccessRules()
        if ($this->module->id != 'admin') {
            $this->adminOnly = false;
        }

        return [
            'acl' => [
                'class' => AccessControl::className(),
                'adminOnly' => $this->adminOnly,
                'rules' => $this->getAccessRules()
            ]
        ];
    }

    /**
     * Returns access rules for the standard access control behavior
     *
     * @see AccessControl
     * @return array the access permissions
     */
    public function getAccessRules()
    {
        // Use by default ManageModule permission, if method is not overwritten by custom module
        if ($this->module->id != 'admin') {
            return [
                ['permissions' => \humhub\modules\admin\permissions\ManageModules::className()]
            ];
        }

        return [];
    }

}
