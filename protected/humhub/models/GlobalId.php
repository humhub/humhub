<?php

/*
 * @link      https://www.humhub.org/
 * @copyright Copyright (c) 2023 HumHub GmbH & Co. KG
 * @license   https://www.humhub.com/licences
 */

namespace humhub\models;

use humhub\components\ActiveRecord;
use humhub\components\behaviors\PolymorphicTrait;
use humhub\libs\UUIDValidator;
use humhub\modules\content\components\ContentContainerActiveRecord;
use Yii;

/**
 * @property $gid int
 * @property $guid string
 * @property $class_map_id int
 * @property $state int
 * @property $url_slug string
 * @property $class
 */
class GlobalId extends ActiveRecord
{
    use PolymorphicTrait;

    /**
     * @var ActiveRecord|null the cached object
     */
    protected ?ActiveRecord $polymorphicRecord = null;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'global_id';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['gid', 'class_map_id', 'state'], 'integer'],
            [['class_map_id'], 'required'],
            [['guid'], UUIDValidator::class, 'skipOnEmpty' => false],
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        // ToDo: Remove when dropping guid fields in related objects

        $activeRecord = $this->getPolymorphicRelation();

        if ($activeRecord !== null) {
            $update = null;

            $guid = $this->guid;
            if ($activeRecord->hasAttribute('guid') && $activeRecord->getAttribute('guid') !== $guid) {
                $update['guid'] = $guid;
            }

            $gid = $this->gid;
            if ($activeRecord->getAttribute('gid') !== $gid) {
                $update['gid'] = $gid;
            }

            if ($update !== null) {
                $activeRecord->updateAttributes($update);
            }

            if (
                $activeRecord instanceof ContentContainerActiveRecord
                && $contentContainer = $activeRecord->contentContainerRecord
            ) {
                $update = null;

                if ($contentContainer->getAttribute('gid') !== $gid) {
                    $update['gid'] = $gid;
                }

                if ($contentContainer->getAttribute('guid') !== $guid) {
                    $update['guid'] = $guid;
                }

                $class = $this->getClass();
                if ($contentContainer->getAttribute('class') !== $class) {
                    $update['class'] = $class;
                }

                if ($update !== null) {
                    $contentContainer->updateAttributes($update);
                }
            }
        }

        parent::afterSave($insert, $changedAttributes);
    }


    /**
     * @param string|object|null $class Class name or object instance to be translated to its ClassID
     *
     * @return GlobalId
     * @see ClassMap::getIdBy()
     */
    public function setClass($class): self
    {
        $this->class_map_id = empty($class) ? null : ClassMap::getIdBy($class);

        return $this;
    }

    /**
     * @return string|null
     */
    public function getClass(): ?string
    {
        return ClassMap::getClassById($this->class_map_id);
    }

    protected function getPolymorphicIdentifier(): array
    {
        return [
            $this->getClass(),
            $this->gid
        ];
    }

    protected function getPolymorphicSettings(): object
    {
        return (object)[
            'classAttribute' => 'class',
            'pkAttribute' => 'gid',
            'strict' => true,
            'mustBeInstanceOf' => [ActiveRecord::class],
        ];
    }
}
