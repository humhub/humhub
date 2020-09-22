<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\permissions;

use humhub\modules\admin\components\BaseAdminPermission;

/**
 * ManageSpaces permission allows access to users/spaces section within the admin area.
 *
 * @since 1.2
 */
class ManageSpaces extends BaseAdminPermission
{
    /**
     * @inheritdoc
     */
    protected $id = 'admin_manage_spaces';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->title = \Yii::t('AdminModule.permissions', 'Manage Spaces');
        $this->description = \Yii::t('AdminModule.permissions', 'Can manage spaces within the \'Administration -> Spaces\' section (create/edit/delete).');
    }

}
