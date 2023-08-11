<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\models;

use humhub\components\behaviors\PolymorphicRelation;
use humhub\components\FindInstanceTrait;
use humhub\interfaces\FindInstanceInterface;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use Yii;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "contentcontainer".
 *
 * @property integer $id
 * @property string $guid
 * @property string $class
 * @property integer $pk
 * @property integer $owner_user_id
 * @property string $tags_cached readonly, a comma separted list of assigned tags
 * @mixin PolymorphicRelation
 */
class ContentContainer extends ActiveRecord implements FindInstanceInterface
{
    use FindInstanceTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contentcontainer';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['pk', 'owner_user_id'], 'integer'],
            [['class', 'pk', 'guid'], 'required'],
            [['guid', 'class'], 'string', 'max' => 255],
            [['class', 'pk'], 'unique', 'targetAttribute' => ['class', 'pk'], 'message' => 'The combination of Class and Pk has already been taken.'],
            [['guid'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'guid' => 'Guid',
            'class' => 'Class',
            'pk' => 'Pk',
            'owner_user_id' => 'Owner User ID',
        ];
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => PolymorphicRelation::class,
                'mustBeInstanceOf' => [ContentContainerActiveRecord::class],
                'classAttribute' => 'class',
                'pkAttribute' => 'pk'
            ]
        ];
    }

    public static function findInstance($identifier, array $config = []): ?self
    {
        $config['stringKey'] ??= 'guid';

        return static::findInstanceHelper($identifier, $config);
    }

    /**
     * @param $guid
     * @return ContentContainerActiveRecord|null
     * @throws \yii\db\IntegrityException
     * @since 1.4
     */
    public static function findRecord($guid)
    {
        $instance = static::findOne(['guid' => $guid]);
        return $instance ? $instance->getPolymorphicRelation() : null;
    }

    public function unsetCache()
    {
        $runtimeCache = Yii::$app->runtimeCache;

        // delete the content record from the cache
        $runtimeCache->delete(static::class . '#' . $this->id);
        $runtimeCache->delete(static::class . '#' . $this->guid);

        /**
         * Check if we have the related record cached in the polymorphic behavior, so we can delete the cache by ID.
         * (This is not fully bullet-proof, as the object might still be saved in the cache, but only under the guid key.)
         *
         * @noinspection PhpUnhandledExceptionInspection
         */
        if (($model = $this->getPolymorphicRelation(false) ?? $runtimeCache->delete($this->class . '#' . $this->pk)) && $model->hasAttribute('guid')) {
            $runtimeCache->delete($this->class . '#' . $model->guid);
        }

        $runtimeCache->delete($this->class . '#' . $this->pk);
    }

    public function afterDelete()
    {
        $this->unsetCache();

        parent::afterDelete();
    }

    public function afterSave($insert, $changedAttributes)
    {
        if (!$insert) {
            $this->unsetCache();
       }

        parent::afterSave($insert, $changedAttributes);
    }
}
