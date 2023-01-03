<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use humhub\libs\BasePermission;
use humhub\modules\user\models\User;
use Yii;
use yii\base\Component;

/**
 * Description of AbstractPermissionManager
 *
 * @since 1.14
 * @author luke
 */
abstract class AbstractPermissionManager extends Component
{
    /**
     * User identity.
     * @var User
     */
    public $subject;

    /**
     * Verifies a given $permission or $permission array for a permission subject.
     *
     * If $params['strict'] is set to true and a $permission array is given all given permissions
     * have to be granted otherwise (default) only one permission test has to pass.
     *
     * @param string|array|BasePermission $permission
     * @param array $params
     * @param boolean $allowCaching
     * @return boolean
     * @throws \yii\base\InvalidConfigException
     */
    abstract public function can($permission, $params = [], $allowCaching = true);

    /**
     * Returns the permission subject identity.
     * If the permission objects $subject property is not set this method returns the currently
     * logged in user identity.
     *
     * @return User|null|bool
     */
    protected function getSubject()
    {
        return $this->subject ?? Yii::$app->user->getIdentity();
    }
}