<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\permissions;

use humhub\modules\admin\components\BaseAdminPermission;
use Yii;

/**
 * Allows managing (e.g. edit or delete) all content (even private)
 *
 * @since 1.17
 */
class ManageAllContent extends BaseAdminPermission
{
    /**
     * @inheritdoc
     */
    protected $id = 'admin_manage_all_content';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->title = Yii::t('AdminModule.permissions', 'Manage All Content');
        $this->description = Yii::t('AdminModule.permissions', 'Can manage (e.g. edit or delete) all content (even private)');
    }
}
