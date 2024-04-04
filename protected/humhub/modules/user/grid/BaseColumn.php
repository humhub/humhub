<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\grid;

use humhub\modules\user\models\User;
use InvalidArgumentException;
use yii\db\ActiveRecord;
use yii\grid\DataColumn;
use yii\helpers\ArrayHelper;

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
     * @param ActiveRecord|array $record
     * @return User the user model
     */
    public function getUser($record)
    {
        $attributeName = $this->userAttribute;

        if ($record instanceof ActiveRecord) {
            if ($attributeName === null) {
                return $record;
            }

            return $record->$attributeName;
        } elseif (is_array($record)) {
            $attribute = $this->userAttribute ?: 'id';

            return User::findOne([$attribute => ArrayHelper::getValue($record, $attribute)]);
        } else {
            throw new InvalidArgumentException();
        }
    }
}
