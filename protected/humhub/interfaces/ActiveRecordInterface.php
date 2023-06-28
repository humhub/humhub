<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\interfaces;

use humhub\modules\file\components\FileManager;
use humhub\modules\user\models\User;

/**
 * Description of ActiveRecord
 *
 * @property FileManager $fileManager
 * @property User        $createdBy
 * @property User        $updatedBy
 * @author luke
 */
interface ActiveRecordInterface
    extends \yii\db\ActiveRecordInterface, BaseActiveRecord
{
    /**
     * @inheritdoc
     */
    public function getAttributeLabel($attribute);

    /**
     * Relation to User defined in created_by attribute
     *
     * @return User|null
     */
    public function getCreatedBy();

    /**
     * Returns the errors as string for all attribute or a single attribute.
     *
     * @param string $attribute attribute name. Use null to retrieve errors for all attributes.
     *
     * @return string the error message
     * @since 1.2
     */
    public function getErrorMessage($attribute = null);

    /**
     * Returns the file manager for this record
     *
     * @return FileManager the file manager instance
     */
    public function getFileManager();

    /**
     * Returns a unique id for this record/model
     *
     * @return String Unique Id of this record
     */
    public function getUniqueId();

    /**
     * Relation to User defined in updated_by attribute
     *
     * @return User|null
     */
    public function getUpdatedBy();

    /**
     * @inheritdoc
     */
    public function afterSave(
        $insert,
        $changedAttributes
    );

    /**
     * @inheritdoc
     */
    public function beforeSave($insert);

    /**
     * @inheritdoc
     */
    public function createValidators();

}
