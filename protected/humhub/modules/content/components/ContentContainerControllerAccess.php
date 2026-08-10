<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\components;

use humhub\components\access\StrictAccess;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use Yii;

/**
 * Class ContentContainerControllerAccess
 *
 * Adds a container permission check to
 *
 * @package components
 */
class ContentContainerControllerAccess extends StrictAccess
{
    public const RULE_SPACE_ONLY = 'space';
    public const RULE_PROFILE_ONLY = 'profile';

    public const RULE_USER_GROUP_ONLY = 'userGroup';

    /**
     * @var ContentContainerActiveRecord
     */
    public $contentContainer;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!$this->contentContainer && Yii::$app->controller instanceof ContentContainerController) {
            $this->contentContainer = Yii::$app->controller->contentContainer;
        }

        // overwrite default permission validator
        $this->registerValidator([ContentContainerPermissionAccess::class, 'contentContainer' => $this->contentContainer]);
        $this->registerValidator([self::RULE_SPACE_ONLY => 'validateSpaceOnlyRule']);
        $this->registerValidator([self::RULE_PROFILE_ONLY => 'validateProfileOnlyRule']);
        $this->registerValidator([UserGroupAccessValidator::class, 'contentContainer' => $this->contentContainer]);
    }

    /**
     * @return bool verifies 'spaceOnly' rules
     */
    public function validateSpaceOnlyRule()
    {
        return $this->isSpaceController();
    }

    /**
     * @return bool verifies 'userOnly' rules
     */
    public function validateProfileOnlyRule()
    {
        return $this->isProfileController();
    }

    /**
     * @inheritdoc
     */
    public function isAdmin()
    {
        if (parent::isAdmin()) {
            return true;
        }

        if ($this->isSpaceController()) {
            return $this->contentContainer->isAdmin($this->user);
        }

        if ($this->isProfileController()) {
            return $this->user && $this->user->is($this->contentContainer);
        }

        return false;
    }

    protected function isSpaceController(): bool
    {
        return $this->contentContainer instanceof Space;
    }

    protected function isProfileController(): bool
    {
        return $this->contentContainer instanceof User;
    }

}
