<?php

/**
 * PollController handles all poll related actions.
 *
 * @package humhub.modules.polls.controllers
 * @since 0.5
 * @author Luke
 */
class PollController extends Controller {

    public $subLayout = "application.modules_core.space.views.space._layout";

    /**
     * @return array action filters
     */
    public function filters() {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules() {
        return array(
            array('allow', // allow authenticated user to perform 'create' and 'update' actions
                'users' => array('@'),
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Add mix-ins to this model
     *
     * @return type
     */
    public function behaviors() {
        return array(
            'SpaceControllerBehavior' => array(
                'class' => 'application.modules_core.space.SpaceControllerBehavior',
            ),
        );
    }

    /**
     * Actions
     *
     * @return type
     */
    public function actions() {
        return array(
            // Adds the PollsStreamAction Module to add own Streaming/Walling
            // for Polls Only Objects.
            'stream' => array(
                'class' => 'application.modules.polls.PollsStreamAction',
                'mode' => 'normal',
            ),
        );
    }

    /**
     * Shows the questions tab
     */
    public function actionShow() {
        $this->render('show', array());
    }

    /**
     * Posts a new question  throu the question form
     *
     * @return type
     */
    public function actionCreate() {

        $workspace = $this->getSpace();

        $this->forcePostRequest();

        if (!$workspace->isMember()) {
            throw new CHttpException(401, 'Access denied!');
        }

        $json = array();
        $json['errorMessage'] = "None";

        $questionText = Yii::app()->request->getParam('question', ""); // content of post
        $answersBlock = Yii::app()->request->getParam('answers', ""); // content of post
        $public = (int) Yii::app()->request->getParam('public', 0);
        $allow_mulitple = (int) Yii::app()->request->getParam('allowMultiple', 0);

        $poll = new Poll();
        $poll->question = Yii::app()->input->stripClean(trim($questionText));

        if ($allow_mulitple == 1) {
            $poll->allow_multiple = 1;
        } else {
            $poll->allow_multiple = 0;
        }

        // Set some content Meta Data
        $poll->contentMeta->space_id = $workspace->id;
        if ($public == 1 && $workspace->canShare()) {
            $poll->contentMeta->visibility = Content::VISIBILITY_PUBLIC;
        } else {
            $poll->contentMeta->visibility = Content::VISIBILITY_PRIVATE;
        }

        if ($poll->save()) {

            $wallEntry = $poll->contentMeta->addToWall($workspace->wall_id);

            // Set Answers
            $answers = explode("\n", $answersBlock);
            foreach ($answers as $answerText) {
                $answer = new PollAnswer();
                $answer->poll_id = $poll->id;
                $answer->answer = Yii::app()->input->stripClean($answerText);
                $answer->save();
            }

            // Build JSON Out
            $json['success'] = true;
            $json['wallEntryId'] = $wallEntry->id;
        } else {
            $json['success'] = false;
            $json['error'] = $poll->getErrors();
        }

        // returns JSON
        echo CJSON::encode($json);
        Yii::app()->end();
    }

    /**
     * Answers a polls
     */
    public function actionAnswer() {
        $poll = $this->getPollByParameter();

        $answers = Yii::app()->request->getParam('answers');

        // Build array of answer ids
        $votes = array();
        if (is_array($answers)) {
            foreach ($answers as $answer_id => $flag) {
                $votes[] = (int) $answer_id;
            }
        } else {
            $votes[] = $answers;
        }

        if (count($votes) > 1 && !$poll->allow_multiple) {
            throw new CHttpException(401, Yii::t('PollsModule.base', 'Voting for multiple answers is disabled!'));
        }

        $poll->vote($votes);
        $this->getPollOut($poll);
    }

    /**
     * Resets users question answers
     */
    public function actionAnswerReset() {
        $poll = $this->getPollByParameter();
        $poll->resetAnswer();
        $this->getPollOut($poll);
    }

    /**
     * Returns a user list including the pagination which contains all results
     * for an answer
     */
    public function actionUserListResults() {

        $poll = $this->getPollByParameter();

        $answerId = (int) Yii::app()->request->getQuery('answerId', '');
        $answer = PollAnswer::model()->findByPk($answerId);
        if ($answer == null || $poll->id != $answer->poll_id) {
            throw new CHttpException(401, Yii::t('PollsModule.base', 'Invalid answer!'));
        }

        $page = (int) Yii::app()->request->getParam('page', 1);
        $total = PollAnswerUser::model()->count('poll_answer_id=:aid', array(':aid' => $answerId));
        $usersPerPage = HSetting::Get('paginationSize');

        $sql = "SELECT u.* FROM `poll_answer_user` a " .
                "LEFT JOIN user u ON a.created_by = u.id " .
                "WHERE a.poll_answer_id=:aid AND u.status=" . User::STATUS_ENABLED . " " .
                "ORDER BY a.created_at DESC " .
                "LIMIT " . ($page - 1) * $usersPerPage . "," . $usersPerPage;
        $params = array(':aid' => $answerId);

        $pagination = new CPagination($total);
        $pagination->setPageSize($usersPerPage);

        $users = User::model()->findAllBySql($sql, $params);
        $output = $this->renderPartial('application.modules_core.user.views._listUsers', array(
            'title' => Yii::t('PollsModule.base', "Users voted for: {answer}", array('{answer}' => $answer->answer)),
            'users' => $users,
            'pagination' => $pagination
                ), true);

        Yii::app()->clientScript->render($output);
        echo $output;
        Yii::app()->end();
    }

    /**
     * Prints the given poll wall output include the affected wall entry id
     *
     * @param Poll $poll
     */
    private function getPollOut($question) {

        $output = $question->getWallOut();
        Yii::app()->clientScript->render($output);

        $json = array();
        $json['output'] = $output;
        $json['wallEntryId'] = $question->contentMeta->getFirstWallEntryId(); // there should be only one
        echo CJSON::encode($json);
        Yii::app()->end();
    }

    /**
     * Returns a given poll by given request parameter.
     *
     * This method also validates access rights of the requested poll object.
     */
    private function getPollByParameter() {

        // Try load space, this also checks access rights and such things
        //$space = $this->getSpace();

        $pollId = (int) Yii::app()->request->getParam('pollId');
        $poll = Poll::model()->findByPk($pollId);

        if ($poll == null) {
            throw new CHttpException(401, Yii::t('PollsModule.base', 'Could not load poll!'));
        }

        if (!$poll->contentMeta->canRead()) {
            throw new CHttpException(401, Yii::t('PollsModule.base', 'You have insufficient permissions to perform that operation!'));
        }

        return $poll;
    }

}