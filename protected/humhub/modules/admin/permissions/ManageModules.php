<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\permissions;

use humhub\modules\admin\components\BaseAdminPermission;

/**
 * ManageModules Permission allows access to module section within the admin area.
 * 
 * @since 1.2
 */
class ManageModules extends BaseAdminPermission
{
    /**
     * @inheritdoc
     */
    protected $id = 'admin_manage_modules';

    public function __construct($config = array())
    {
        parent::__construct($config);
        $this->title = \Yii::t('AdminModule.permissions', 'Manage Modules');
        $this->description = \Yii::t('AdminModule.permissions', 'Cann manage modules within the \'Administration ->  Modules\' section.');
    }

}
