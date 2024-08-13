<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\admin\grid;

use yii\db\ActiveRecord;
use yii\grid\DataColumn;

/**
 * BaseColumn for space grid fields
 *
 * @since 1.3
 * @author Luke
 */
abstract class SpaceBaseColumn extends DataColumn
{

    /**
     * @var string|null name of space model attribute
     */
    public $spaceAttribute = null;

    /**
     * Returns the space record 
     * 
     * @param ActiveRecord $record
     * @return \humhub\modules\space\models\Space the space model
     */
    public function getSpace(ActiveRecord $record)
    {
        if ($this->spaceAttribute === null) {
            return $record;
        }

        $attributeName = $this->spaceAttribute;
        return $record->$attributeName;
    }

}
