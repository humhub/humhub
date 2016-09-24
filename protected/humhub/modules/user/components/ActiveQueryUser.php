<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\components;

use yii\db\ActiveQuery;

/**
 * Description of ActiveQueryUser
 *
 * @author luke
 */
class ActiveQueryUser extends ActiveQuery
{

    /**
     * Limit to active users
     * 
     * @return \humhub\modules\user\components\ActiveQueryUser
     */
    public function active()
    {
        $this->andWhere(['user.status' => \humhub\modules\user\models\User::STATUS_ENABLED]);
        return $this;
    }

    /**
     * Adds default user order (e.g. by lastname)
     * 
     * @return \humhub\modules\user\components\ActiveQueryUser
     */
    public function defaultOrder()
    {
        $this->joinWith('profile');
        $this->addOrderBy(['profile.lastname' => SORT_ASC]);
        return $this;
    }

}
