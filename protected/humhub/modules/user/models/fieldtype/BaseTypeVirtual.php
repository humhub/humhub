<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\modules\user\models\User;

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
    public $type = 'hidden';

    /**
     * @inheritdoc
     */
    public $isVirtual = true;


    /**
     * @inheritdoc
     */
    final public function getUserValue(User $user, bool $raw = true, bool $encode = true): ?string
    {
        return $this->getVirtualUserValue($user, $raw, $encode);
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
                'elements' => [],
            ]]);
    }

    /**
     * @inheritdoc
     */
    public function getFieldFormDefinition(User $user = null, array $options = []): array
    {
        return parent::getFieldFormDefinition($user, array_merge([
            'isVisible' => false,
        ], $options));
    }

    /**
     * @inheritdoc
     */
    protected static function getHiddenFormFields()
    {
        return ['searchable', 'required', 'show_at_registration', 'editable', 'directory_filter'];
    }

    /**
     * Returns the readonly virtual value for the given User
     *
     * @param User $user
     * @param bool $raw
     * @param bool $encode
     * @return string
     */
    abstract protected function getVirtualUserValue(User $user, bool $raw = true, bool $encode = true): string;

    /**
     * @inheritDoc
     */
    public function save()
    {
        $this->profileField->editable = 0;
        $this->profileField->searchable = 0;
        $this->profileField->required = 0;
        $this->profileField->show_at_registration = 0;
        $this->profileField->directory_filter = 0;
        return parent::save();
    }


}
