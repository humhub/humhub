<?php

namespace humhub\core\activity\models;

use Yii;
use yii\web\HttpException;

/**
 * This is the model class for table "activity".
 *
 * @property integer $id
 * @property string $type
 * @property string $module
 * @property string $object_model
 * @property string $object_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 */
class Activity extends \humhub\core\content\components\activerecords\Content
{

    /**
     * Add mix-ins to this model
     *
     * @return type
     */
    public function behaviors()
    {
        return [
            [
                'class' => \humhub\components\behaviors\UnderlyingObject::className(),
                'mustBeInstanceOf' => [
                    \humhub\core\content\components\activerecords\Content::className(),
                    \humhub\core\content\components\activerecords\ContentContainer::className(),
                    \humhub\core\content\components\activerecords\ContentAddon::className(),
                ]
            ]
        ];
    }

    public function getWallOut()
    {
        $activity = $this->getClass();


        return $activity->render();
    }

    public function getMailOut()
    {
        
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
            [['created_at', 'updated_at'], 'safe'],
            [['created_by', 'updated_by', 'object_id'], 'integer'],
            [['type'], 'string', 'max' => 45],
            [['module', 'object_model'], 'string', 'max' => 100]
        ];
    }

    /**
     * @return \humhub\core\notification\components\BaseNotification
     */
    public function getClass()
    {
        if (class_exists($this->type)) {
            return Yii::createObject([
                        'class' => $this->type,
                        'record' => $this,
                        'originator' => $this->content->user,
                        'source' => $this->getUnderlyingObject(),
            ]);
        }
        return null;
    }

}
