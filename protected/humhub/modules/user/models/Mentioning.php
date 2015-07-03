<?php

namespace humhub\modules\user\models;

use humhub\modules\content\components\activerecords\Content;
use humhub\modules\content\components\activerecords\ContentAddon;

/**
 * This is the model class for table "user_mentioning".
 *
 * The followings are the available columns in table 'user_mentioning':
 * @property integer $id
 * @property string $object_model
 * @property integer $object_id
 * @property integer $user_id
 */
class Mentioning extends \humhub\components\ActiveRecord
{

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'user_mentioning';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array(
            [['object_model', 'object_id', 'user_id'], 'required'],
            [['object_id', 'user_id'], 'integer'],
            [['object_model'], 'string', 'max' => 100]
        );
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            [
                'class' => \humhub\components\behaviors\UnderlyingObject::className(),
                'mustBeInstanceOf' => [Content::className(), ContentAddon::className()],
            ],
        ];
    }

    public function afterSave($insert, $changedAttributes)
    {
        $object = $this->getUnderlyingObject();

        // Send mentioned notification
        $notification = new \humhub\modules\user\notifications\Mentioned;
        $notification->source = $object;
        if ($object instanceof Content) {
            $notification->originator = $object->content->user;
        } elseif ($object instanceof ContentAddon) {
            $notification->originator = $object->user;
        } else {
            throw new \yii\base\Exception("Underlying object invalid!");
        }
        $notification->send($this->user);

        return parent::afterSave($insert, $changedAttributes);
    }

    /**
     * Parses a given text for mentioned users and creates an mentioning for them.
     *
     * @param HActiveRecordContent|HActiveRecordContentAddon $record
     * @param string $text
     */
    public static function parse($record, $text)
    {

        if ($record instanceof Content || $record instanceof ContentAddon) {

            preg_replace_callback('@\@\-u([\w\-]*?)($|\s|\.)@', function($hit) use(&$record) {
                $user = User::findOne(['guid' => $hit[1]]);
                if ($user !== null) {
                    // Check the user was already mentioned (e.g. edit)
                    $mention = self::findOne(['object_model' => get_class($record), 'object_id' => $record->getPrimaryKey(), 'user_id' => $user->id]);
                    if ($mention === null) {

                        $mention = new Mentioning;
                        $mention->object_model = $record->className();
                        $mention->object_id = $record->getPrimaryKey();
                        $mention->user_id = $user->id;
                        $mention->save();
                        $mention->setUnderlyingObject($record);

                        // Mentioned users automatically follows the content
                        $record->content->getUnderlyingObject()->follow($user->id);
                    }
                }
            }, $text);
        } else {
            throw new Exception("Mentioning can only used in HActiveRecordContent or HActiveRecordContentAddon objects!");
        }
    }

    public function getUser()
    {
        return $this->hasOne(\humhub\modules\user\models\User::className(), ['id' => 'user_id']);
    }

}
