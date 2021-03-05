<?php
/**
 * @link https://coinsence.org/
 * @copyright Copyright (c) 2020 Coinsence
 * @license https://www.humhub.com/licences
 *
 * @author Daly Ghaith <daly.ghaith@gmail.com>
 */

namespace humhub\modules\admin\permissions;

use humhub\modules\admin\components\BaseAdminPermission;
use Yii;

/**
 * ManageTags permission allows access to tags section within the admin area.
 */
class ManageTags extends BaseAdminPermission
{
    /**
     * @inheritdoc
     */
    protected $id = 'admin_manage_tags';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->title = Yii::t('AdminModule.permissions', 'Manage Tags');
        $this->description = Yii::t('AdminModule.permissions', 'Can manage tags within the \'Administration -> tags\' section (create/delete).');
    }

}
