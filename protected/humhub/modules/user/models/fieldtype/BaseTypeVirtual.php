<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

/**
 * Base type for virtual profile fields
 *
 * Virtual profile fields are read-only and can be used to display content
 * from other sources (e.g. user table).
 *
 * @since 1.6
 */
abstract class BaseTypeVirtual extends BaseType
{

    /**
     * @inheritdoc
     */
    public $isVirtual = true;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function getUserValue($user, $raw = true)
    {
        // TODO: Implement getUserValue() method.
    }

    /**
     * @inheritDoc
     */
    public function getFormDefinition($definition = [])
    {
        return parent::getFormDefinition([
            get_class($this) => [
                'type' => 'form',
                'title' => '',
                'elements' => []
            ]]);
    }

    /**
     * @inheritdoc
     */
    public function getFieldFormDefinition()
    {
        return [$this->profileField->internal_name => [
            'type' => 'hidden',
            'isVisible' => false,
        ]];
    }
}
