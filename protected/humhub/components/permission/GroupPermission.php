<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\permission;

use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Exception;

/**
 * BasePermission
 * @author luke
 */
class GroupPermission extends BasePermission
{
    /**
     * Permission States
     */
    const STATE_DEFAULT = '';
    const STATE_ALLOW = 1;
    const STATE_DENY = 0;


    /**
     * A list of groupIds which allowed per default.
     *
     * @var array default allowed groups
     */
    protected $defaultAllowedGroups = [
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
    ];

    /**
     * A list of groupIds which are fixed group state.
     * See defaultState for default setting.
     *
     * @var array default fixed groups
     */
    protected $fixedGroups = [
        Space::USERGROUP_GUEST,
        Space::USERGROUP_OWNER,
        Space::USERGROUP_ADMIN,
    ];

    /**
     * The default state of this permission
     *
     * @var string
     */
    protected $defaultState = self::STATE_DENY;

    /**
     * Optional contentContainer instance to improve title and description.
     *
     * @since 1.2
     * @var \humhub\modules\content\components\ContentContainerActiveRecord
     */
    public $contentContainer = null;


    /**
     * Returns the default state of the permission.
     * The defaultState is either defined by setting $defaultState attribute
     * or by overwriting the $defaultState by means of the configuration param 'defaultPermissions'.
     *
     * If the $defaultState is set to denied, we can grant the permission for specific groups by defining
     * the $defaultAllowedGroups array.
     *
     * @return int the default state
     */
    public function getDefaultState($groupId)
    {
        $configuredState = $this->getConfiguredState($groupId);
        if ($configuredState !== null) {
            return $configuredState;
        }

        if ($this->defaultState == self::STATE_ALLOW) {
            return self::STATE_ALLOW;
        }

        return (int)(in_array($groupId, $this->defaultAllowedGroups));
    }

    /**
     * Returns the default state set in the configration params 'defaultPermissions'.
     * This method returns null in case the default state for this permission or group is not set in
     * the configuration.
     *
     * @param int $groupId
     * @return int|null
     * @since 1.2
     */
    protected function getConfiguredState($groupId)
    {
        if (!isset(Yii::$app->params['defaultPermissions'][static::class])) {
            return null;
        }

        if (isset(Yii::$app->params['defaultPermissions'][static::class][$groupId])) {
            return Yii::$app->params['defaultPermissions'][static::class][$groupId];
        }

        // Allow asterisk to overwrite all groups excluding guest groups
        if (isset(Yii::$app->params['defaultPermissions'][static::class]['*'])
            && !in_array($groupId, [Space::USERGROUP_GUEST, User::USERGROUP_GUEST], true)) {
            return Yii::$app->params['defaultPermissions'][static::class]['*'];
        }

        return null;
    }

    /**
     * Checks if permission state can be changed
     *
     * @return boolean
     */
    public function canChangeState($groupId)
    {
        return (!in_array($groupId, $this->fixedGroups));
    }


    /**
     * Returns the label for given State
     *
     * @return string the label
     * @throws Exception
     */
    public static function getLabelForState($state)
    {
        if ($state === self::STATE_ALLOW) {
            return Yii::t('base', 'Allow');
        } elseif ($state === self::STATE_DENY) {
            return Yii::t('base', 'Deny');
        } elseif ($state == '') {
            return Yii::t('base', 'Default');
        }

        throw new Exception('Invalid permission state');
    }

    /**
     * @param array Ids of additional fixed groups
     */
    public function addFixedGroups($groupIds)
    {
        if (is_array($groupIds) && !empty($groupIds)) {
            $this->fixedGroups = array_merge($this->fixedGroups, $groupIds);
        }
    }
}
