<?php

/**
 * This is the model class for table "poll".
 *
 * The followings are the available columns in table 'poll':
 *
 * @property integer $id
 * @property string $question
 * @property integer $allow_multiple
 * @property string $created_at
 * @property integer $created_by
 * @property string $updated_at
 * @property integer $updated_by
 *
 * @package humhub.modules.polls.models
 * @since 0.5
 * @author Luke
 */
class Poll extends HActiveRecordContent {

    /**
     * Returns the static model of the specified AR class.
     * @param string $className active record class name.
     * @return Question the static model class
     */
    public static function model($className = __CLASS__) {
        return parent::model($className);
    }

    /**
     * @return string the associated database table name
     */
    public function tableName() {
        return 'poll';
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules() {
        // NOTE: you should only define rules for those attributes that
        // will receive user inputs.
        return array(
            array('question, created_at, created_by, updated_at, updated_by', 'required'),
            array('allow_multiple, created_by, updated_by', 'numerical', 'integerOnly' => true),
        );
    }

    /**
     * @return array relational rules.
     */
    public function relations() {
        return array(
            'answers' => array(self::HAS_MANY, 'PollAnswer', 'poll_id'),
        );
    }

    protected function afterSave() {
        parent::afterSave();

        if ($this->isNewRecord) {

            // Create Question Answered Activity
            $activity = Activity::CreateForContent($this);
            $activity->type = "PollCreated";
            $activity->module = "polls";
            $activity->save();
            $activity->fire();
        }

        return true;
    }

    /**
     * Deletes a Poll including its dependencies.
     */
    public function beforeDelete() {

        // Delete all dependencies
        foreach ($this->answers as $answer) {
            foreach ($answer->votes as $answerUser) {
                $answerUser->delete();
            }
            $answer->delete();
        }

        return parent::beforeDelete();
    }

    /**
     * Checks if user has voted
     *
     * @param type $userId
     * @return type
     */
    public function hasUserVoted($userId = "") {

        if ($userId == "")
            $userId = Yii::app()->user->id;

        $answer = PollAnswerUser::model()->findByAttributes(array('created_by' => $userId, 'poll_id' => $this->id));

        if ($answer == null)
            return false;

        return true;
    }

    public function vote($votes = array()) {

        if ($this->hasUserVoted()) {
            return;
        }

        $voted = false;

        foreach ($votes as $answerId) {
            $answer = PollAnswer::model()->findByAttributes(array('id' => $answerId, 'poll_id' => $this->id));

            $userVote = new PollAnswerUser();
            $userVote->poll_id = $this->id;
            $userVote->poll_answer_id = $answer->id;

            if ($userVote->save()) {
                $voted = true;
            }
        }

        if ($voted) {
            // Create Question Answered Activity
            $activity = Activity::CreateForContent($this);
            $activity->type = "PollAnswered";
            $activity->module = "polls";
            $activity->save();
            $activity->fire();
        }
    }

    /**
     * Resets all answers from a user
     *
     * @param type $userId
     */
    public function resetAnswer($userId = "") {

        if ($userId == "")
            $userId = Yii::app()->user->id;

        if ($this->hasUserVoted($userId)) {

            $answers = PollAnswerUser::model()->findAllByAttributes(array('created_by' => $userId, 'poll_id' => $this->id));
            foreach ($answers as $answer) {
                $answer->delete();
            }

            // Delete Activity for Question Answered
            $activity = Activity::model()->findByAttributes(array(
                'type' => 'PollAnswered',
                'object_model' => "Poll",
                'created_by' => $userId,
                'object_id' => $this->id
            ));

            if ($activity)
                $activity->delete();
        }
    }

    /**
     * Returns the Wall Output
     */
    public function getWallOut() {
        return Yii::app()->getController()->widget('application.modules.polls.widgets.PollWallEntryWidget', array('poll' => $this), true);
    }

    /**
     * Returns a title/text which identifies this IContent.
     *
     * e.g. Post: foo bar 123...
     *
     * @return String
     */
    public function getContentTitle() {
        return Yii::t('PollsModule.base', "Question") . " \"" . Helpers::truncateText($this->question, 25) . "\"";
    }

}