<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models;

use humhub\components\ActiveRecord;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;

/**
 * This is the model class for table "user_mentioning".
 *
 * The followings are the available columns in table 'user_mentioning':
 * @property integer $id
 * @property string $object_model
 * @property integer $object_id
 * @property integer $user_id
 */
class Mentioning extends ActiveRecord
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
                'class' => \humhub\components\behaviors\PolymorphicRelation::className(),
                'mustBeInstanceOf' => [ContentActiveRecord::className(), ContentAddonActiveRecord::className()],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function afterSave($insert, $changedAttributes)
    {
        $mentionedSource = $this->getPolymorphicRelation();

        // Send mentioned notification
        $notification = new \humhub\modules\user\notifications\Mentioned;
        $notification->source = $mentionedSource;
        if ($mentionedSource instanceof ContentActiveRecord) {
            $notification->originator = $mentionedSource->content->user;
        } elseif ($mentionedSource instanceof ContentAddonActiveRecord) {
            $notification->originator = $mentionedSource->user;
        } else {
            throw new \yii\base\Exception("Invalid polymorphic relation!");
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

        if ($record instanceof ContentActiveRecord || $record instanceof ContentAddonActiveRecord) {

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
                        $mention->setPolymorphicRelation($record);

                        // Mentioned users automatically follows the content
                        $record->content->getPolymorphicRelation()->follow($user->id);
                    }
                }
            }, $text);
        } else {
            throw new Exception("Mentioning can only used in HActiveRecordContent or HActiveRecordContentAddon objects!");
        }
    }

    /**
     * Related user
     * 
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(\humhub\modules\user\models\User::className(), ['id' => 'user_id']);
    }

}
