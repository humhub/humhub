<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
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

    public function init()
    {
        parent::init();
    }

    public function active()
    {
        $this->andWhere(['user.status' => \humhub\modules\user\models\User::STATUS_ENABLED]);
        return $this;
    }

}
