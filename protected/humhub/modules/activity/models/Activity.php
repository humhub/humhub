<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\activity\components\ActivityWebRenderer;
use humhub\components\behaviors\PolymorphicRelation;

/**
 * This is the model class for table "activity".
 *
 * @property integer $id
 * @property string $class
 * @property string $module
 * @property string $object_model
 * @property integer $object_id
 *
 * @mixin PolymorphicRelation
 */
class Activity extends ContentActiveRecord
{

    /**
     * @inheritdoc
     */
    public $wallEntryClass = 'humhub\modules\activity\widgets\Activity';

    /**
     * @inheritdoc
     */
    public $autoFollow = false;

    /**
     * @inheritdoc
     */
    protected $streamChannel = 'activity';

    /**
     * @inheritdoc
     */
    public $silentContentCreation = true;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => PolymorphicRelation::class,
                'strict' => true,
                'mustBeInstanceOf' => [
                    ActiveRecord::class,
                ]
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'activity';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['object_id'], 'integer'],
            [['class'], 'string', 'max' => 100],
            [['module', 'object_model'], 'string', 'max' => 100]
        ];
    }

    /**
     * Returns the related BaseActivity object of this Activity record.
     *
     * @return \humhub\modules\activity\components\BaseActivity
     */
    public function getActivityBaseClass()
    {
        if (class_exists($this->class)) {
            $result = Yii::createObject([
                'class' => $this->class,
                'originator' => $this->content->createdBy,
                'source' => $this->getSource(),
            ]);
            $result->record = $this; // If we include the record in createObject, it somehow loses activerecord data (id etc...)
            return $result;
        } else {
            throw new Exception('Could not find BaseActivity ' . $this->class . ' for Activity Record.');
        }
    }

    /**
     * @inheritdoc
     */
    public function getWallOut($params = [])
    {
        $cacheKey = 'activity_wall_out_' . Yii::$app->language . '_' . $this->id;
        $output = false;

        if ($output === false) {
            $activity = $this->getActivityBaseClass();
            if ($activity !== null) {
                $renderer = new ActivityWebRenderer();
                $output = $renderer->render($activity);
                Yii::$app->cache->set($cacheKey, $output);
                return $output;
            }
        }

        return $output;
    }

    /**
     * Returns the source object which belongs to this Activity.
     *
     * @see \humhub\modules\activity\components\BaseActivity::$source
     * @return mixed
     * @throws \yii\db\IntegrityException
     */
    public function getSource()
    {
        return $this->getPolymorphicRelation();
    }
}
