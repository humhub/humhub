<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\permissions;

use humhub\libs\BasePermission;
use humhub\modules\space\models\Space;
use Yii;

/**
 * Manage users to space without inviting them.
 */
class MembersManagePermission extends BasePermission
{
    /**
     * @inheritdoc
     */
    protected $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
    ];

    /**
     * @inheritdoc
     */
    protected $fixedGroups = [
        Space::USERGROUP_USER,
        Space::USERGROUP_MEMBER,
        Space::USERGROUP_GUEST,
    ];

    /**
     * @inheritdoc
     */
    protected $title = 'Manage members';

    /**
     * @inheritdoc
     */
    protected $description = 'Allows to able to directly add selected users to space without inviting them';

    /**
     * @inheritdoc
     */
    protected $moduleId = 'space';

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->title = Yii::t('SpaceModule.permissions', 'Manage members');

        $this->description = Yii::t(
            'SpaceModule.permissions',
            'Allows to able to directly add selected users to space without inviting them'
        );
    }
}
