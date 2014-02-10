<?php

/**
 * This is the model class for table "poll_answer_user".
 *
 * The followings are the available columns in table 'poll_answer_user':
 * @property integer $id
 * @property integer $question_id
 * @property integer $question_answer_id
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @package humhub.modules.polls.models
 * @since 0.5
 * @author Luke
 */
class PollAnswerUser extends HActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return QuestionAnswerUser the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'poll_answer_user';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('poll_answer_id, poll_id, created_at, created_by, updated_at, updated_by', 'required'),
            array('poll_answer_id, poll_id, created_by, updated_by', 'numerical', 'integerOnly' => true),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        return array(
            'poll' => array(self::BELONGS_TO, 'poll', 'id'),
            'user' => array(self::BELONGS_TO, 'User', 'created_by'),
        );
    }

}