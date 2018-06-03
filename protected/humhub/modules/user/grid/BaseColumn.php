<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\grid;

use yii\db\ActiveRecord;
use yii\grid\DataColumn;

/**
 * BaseColumn for user grid fields
 *
 * @since 1.3
 * @author Luke
 */
abstract class BaseColumn extends DataColumn
{

    /**
     * @var string|null name of user attribute
     */
    public $userAttribute = null;

    /**
     * Returns the user record 
     * 
     * @param ActiveRecord $record
     * @return \humhub\modules\user\models\User the user model
     */
    public function getUser(ActiveRecord $record)
    {
        if ($this->userAttribute === null) {
            return $record;
        }

        $attributeName = $this->userAttribute;
        return $record->$attributeName;
    }

}
