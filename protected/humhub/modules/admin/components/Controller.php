<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\components;

use humhub\components\behaviors\AccessControl;
use humhub\modules\admin\permissions\ManageSettings;
use Yii;

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
     * @var bool if true only allows access for system admins else the access is restricted by getAccessRules()
     */
    public $adminOnly = true;

    public $loggedInOnly = true;

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
                'class' => AccessControl::class,
                'adminOnly' => $this->adminOnly,
                'rules' => $this->getAccessRules(),
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        // Use by default ManageSettings permission if method is not overwritten by custom module
        if ($this->module->id !== 'admin') {
            return [
                ['permission' => ManageSettings::class],
            ];
        }

        return [];
    }

}
