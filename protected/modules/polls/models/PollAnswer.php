<?php

/**
 * This is the model class for table "poll_answer".
 *
 * The followings are the available columns in table 'poll_answer':
 * @property integer $id
 * @property integer $question_id
 * @property string $answer
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @package humhub.modules.polls.models
 * @since 0.5
 * @author Luke
 */
class PollAnswer extends HActiveRecord {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return QuestionAnswer the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'poll_answer';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('poll_id, answer, created_at, created_by, updated_at, updated_by', 'required'),
            array('poll_id, created_by, updated_by', 'numerical', 'integerOnly' => true),
            array('answer', 'length', 'max' => 255),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        return array(
            'poll' => array(self::BELONGS_TO, 'Poll', 'id'),
            'votes' => array(self::HAS_MANY, 'PollAnswerUser', 'poll_answer_id'),
        );
    }

    /**
     * Returns the percentage of users voted for this answer
     *
     * @return int
     */
    public function getPercent() {

        $total = PollAnswerUser::model()->countByAttributes(array('poll_id' => $this->poll_id));
        if ($total == 0)
            return 0;

        $me = PollAnswerUser::model()->countByAttributes(array('poll_answer_id' => $this->id));
        return $me / $total * 100;
    }

    /**
     * Returns the total number of users voted for this answer
     *
     * @return int
     */
    public function getTotal() {

        return PollAnswerUser::model()->countByAttributes(array('poll_answer_id' => $this->id));
    }

}