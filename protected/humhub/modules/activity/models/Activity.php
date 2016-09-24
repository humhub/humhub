<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\models;

use Yii;
use yii\base\Exception;
use humhub\modules\content\components\ContentActiveRecord;

/**
 * This is the model class for table "activity".
 *
 * @property integer $id
 * @property string $class
 * @property string $module
 * @property string $object_model
 */
class Activity extends ContentActiveRecord
{

    /**
     * @inheritdoc
     */
    public $wallEntryClass = "humhub\modules\activity\widgets\Activity";

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => \humhub\components\behaviors\PolymorphicRelation::className(),
                'mustBeInstanceOf' => [
                    \yii\db\ActiveRecord::className(),
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
            return Yii::createObject([
                        'class' => $this->class,
                        'record' => $this,
                        'originator' => $this->content->user,
                        'source' => $this->getSource(),
            ]);
        } else {
            throw new Exception("Could not find BaseActivity " . $this->class . " for Activity Record.");
        }
    }

    /**
     * @inheritdoc
     */
    public function getWallOut($params = [])
    {
        $cacheKey = 'activity_wall_out_' . Yii::$app->language . '_' . $this->id;
        $output = Yii::$app->cache->get($cacheKey);

        if ($output === false) {
            $activity = $this->getActivityBaseClass();
            if ($activity !== null) {
                $output = $activity->render();
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
     */
    public function getSource()
    {
        return $this->getPolymorphicRelation();
    }

}
