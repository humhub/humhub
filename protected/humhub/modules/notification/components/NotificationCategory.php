<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\notification\components;

use yii\base\InvalidConfigException;
use humhub\modules\user\models\User;
use humhub\modules\notification\targets\BaseTarget;
use humhub\modules\notification\targets\MailTarget;
use humhub\modules\notification\targets\WebTarget;
use humhub\modules\notification\targets\MobileTarget;

/**
 * NotificationCategories are used to group different notifications in views and
 * configure the notifications in the notification settings.
 * 
 */
abstract class NotificationCategory extends \yii\base\Object
{

    /**
     * @var string the category id 
     */
    public $id;

    /**
     * @var int used to sort categories
     */
    public $sortOrder = PHP_INT_MAX;

    /**
     * @inheritdoc
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();
        if (!$this->id) {
            throw new InvalidConfigException('NotificationCategories have to define an id property, which is not the case for "' . self::class . '"');
        }
    }

    /**
     * Returns a human readable title of this  category
     */
    public abstract function getTitle();

    /**
     * Returns a group description
     */
    public abstract function getDescription();

    /**
     * Returns the default enabled settings for the given $target.
     * In case the $target is unknown, subclasses can either return $target->defaultSetting
     * or another default value.
     * 
     * @param BaseTarget $target
     * @return boolean
     */
    public function getDefaultSetting(BaseTarget $target)
    {
        if ($target->id === MailTarget::getId()) {
            return true;
        } else if ($target->id === WebTarget::getId()) {
            return true;
        } else if ($target->id === MobileTarget::getId()) {
            return false;
        }

        return $target->defaultSetting;
    }

    /**
     * Returns an array of target ids, which are not editable.
     * 
     * @param BaseTarget $target
     */
    public function getFixedSettings()
    {
        return [];
    }

    /**
     * Checks if the given notification target is fixed for this category.
     * 
     * @param type $target
     * @return type
     */
    public function isFixedSetting(BaseTarget $target)
    {
        return in_array($target->id, $this->getFixedSettings());
    }

    /**
     * Determines if this category is visible for the given $user.
     * This can be used if a category is only visible for users with certian permissions.
     * 
     * Note if no user is given this function should return true in most cases, otherwise this
     * category won't be visible in the global notification settings.
     * 
     * @param User $user
     * @return boolean
     */
    public function isVisible(User $user = null)
    {
        return true;
    }

}
