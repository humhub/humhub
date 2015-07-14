<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\activity\models;

use Yii;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;

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
    public function behaviors()
    {
        return [
            [
                'class' => \humhub\components\behaviors\PolymorphicRelation::className(),
                'mustBeInstanceOf' => [
                    ContentActiveRecord::className(),
                    ContentContainerActiveRecord::className(),
                    ContentAddonActiveRecord::className(),
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
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getWallOut()
    {
        $output = Yii::$app->cache->get('activity_wall_out_' . $this->id);

        if ($output === false) {
            $activity = $this->getActivityBaseClass();
            if ($activity !== null) {
                $output = $activity->render();
                Yii::$app->cache->set('activity_wall_out_' . $this->id, $output);
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
