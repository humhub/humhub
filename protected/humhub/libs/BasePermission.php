<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;
use humhub\modules\space\models\Space;

/**
 * BasePermission

 * @author luke
 */
class BasePermission extends \yii\base\Object
{

    /**
     * Permission States
     */
    const STATE_DEFAULT = '';
    const STATE_ALLOW = 1;
    const STATE_DENY = 0;

    /**
     * @var string id of the permission (default is classname)
     */
    protected $id;

    /**
     * @var string title of the permission
     */
    protected $title = "";

    /**
     * @var string description of the permission
     */
    protected $description = "";

    /**
     * @var string module id which belongs to the permission
     */
    protected $moduleId = "";

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
     * Returns the ID
     *
     * @return string the id of the permission
     */
    public function getId()
    {
        if ($this->id != "") {
            return $this->id;
        }

        return $this->className();
    }

    /**
     * Returns the title
     *
     * @return string the title of the permission
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Returns the description
     *
     * @return string the description of the permission
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Returns the module id
     *
     * @return string the moduleid of the permission
     */
    public function getModuleId()
    {
        return $this->moduleId;
    }

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
        } else if ($this->defaultState == self::STATE_ALLOW) {
            return self::STATE_ALLOW;
        } else {
            return (int) (in_array($groupId, $this->defaultAllowedGroups));
        }
    }

    /**
     * Returns the default state set in the configration params 'defaultPermissions'.
     * This method returns null in case the default state for this permission or group is not set in
     * the configuration.
     *
     * @param type $groupId
     * @return type
     * @since 1.2
     */
    protected function getConfiguredState($groupId)
    {
        if (isset(Yii::$app->params['defaultPermissions'][self::className()]) && isset(Yii::$app->params['defaultPermissions'][self::className()][$groupId])) {
            return Yii::$app->params['defaultPermissions'][self::className()][$groupId];
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
     * Checks the given id belongs to this permission
     *
     * @return boolean
     */
    public function hasId($id)
    {
        return ($this->getId() == $id);
    }

    /**
     * Returns the label for given State
     *
     * @return string the label
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

        throw new \yii\base\Exception('Invalid permission state');
    }

}
