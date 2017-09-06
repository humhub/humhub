<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

/**
 * Created by PhpStorm.
 * User: buddha
 * Date: 30.07.2017
 * Time: 04:04
 */

namespace humhub\modules\content\components;


use humhub\components\access\ActionAccessValidator;
use humhub\libs\BasePermission;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidParamException;

class UserGroupAccessValidator extends ActionAccessValidator
{
    public $name = 'userGroup';

    /**
     * @var ContentContainerActiveRecord
     */
    public $contentContainer;

    public $strict = false;

    private $spaceGroupLevel = [
        Space::USERGROUP_GUEST,
        Space::USERGROUP_USER,
        Space::USERGROUP_MEMBER,
        Space::USERGROUP_MODERATOR,
        Space::USERGROUP_ADMIN,
        Space::USERGROUP_OWNER,
    ];

    private $profileGroupLevel = [
        User::USERGROUP_GUEST,
        User::USERGROUP_USER,
        User::USERGROUP_FRIEND,
        User::USERGROUP_SELF,
    ];

    protected function validate($rule)
    {
        if (isset($rule[$this->name]) && !empty($rule[$this->name])) {
            $allowedGroups = is_string($rule[$this->name]) ? [$rule[$this->name]] : $rule[$this->name];
            $userGroup = $this->contentContainer->getUserGroup($this->access->user);

            if(isset($rule['strict']) && $rule['strict'] == true) {
                return in_array($userGroup, $allowedGroups);
            }

            foreach ($allowedGroups as $allowedUserGroup) {
                if($this->getUserGroupLevel($userGroup) >= $this->getUserGroupLevel($allowedUserGroup)) {
                    return true;
                }
            }

            return false;
        }

        throw new InvalidParamException('Invalid userGroup rule provided for action ' . $this->action);
    }

    public function getUserGroupLevel($userGroup)
    {
        $userGroupLevelArr = ($this->contentContainer instanceof Space) ? $this->spaceGroupLevel : $this->profileGroupLevel;

        if(!in_array($userGroup, $userGroupLevelArr)) {
            return PHP_INT_MAX;
        }

        return array_search($userGroup, $userGroupLevelArr);
    }

    protected function extractActions($rule)
    {
        $actions = null;

        if (isset($rule['actions'])) {
            $actions = $rule['actions'];
        }

        return $actions;
    }

    public function getReason()
    {
        return Yii::t('error', 'You are not permitted to access this section.');
    }
}