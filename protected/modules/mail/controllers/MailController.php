<?php

/**
 * MailController provides messaging actions.
 *
 * @package humhub.modules.mail.controllers
 * @since 0.5
 */
class MailController extends Controller
{

    /**
     * @var String sublayout view to use
     */
    public $subLayout = "_layout";

    /**
     * @return array action filters
     */
    public function filters()
    {
        return array(
            'accessControl', // perform access control for CRUD operations
        );
    }

    /**
     * Specifies the access control rules.
     * This method is used by the 'accessControl' filter.
     * @return array access control rules
     */
    public function accessRules()
    {
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
     * Overview of all messages
     */
    public function actionIndex()
    {

        $mailsPerPage = 10;

        // Current page
        $page = (int)Yii::app()->request->getParam('page', 1);

        // Count all Message
        $allMessageCount = UserMessage::model()->countByAttributes(array('user_id' => Yii::app()->user->id));

        $sql = "SELECT user_message.*
		FROM user_message
		LEFT JOIN message on message.id = user_message.message_id
		WHERE  user_message.user_id = :userId
		ORDER BY message.updated_at DESC
		LIMIT " . intval(($page - 1) * $mailsPerPage) . "," . intval($mailsPerPage);

        $pages = new CPagination($allMessageCount);
        $pages->setPageSize($mailsPerPage);

        $userMessages = UserMessage::model()->findAllBySql($sql, array(":userId" => Yii::app()->user->id));

        $this->render('/mail/index', array(
            'userMessages' => $userMessages,
            'mailCount' => $allMessageCount,
            'pageSize' => $mailsPerPage,
            'pages' => $pages
        ));

    }

    /**
     * Overview of all messages
     *
     *
     */
    public function actionList()
    {

        $mailsPerPage = 5;

        // Current page
        $page = (int)Yii::app()->request->getParam('page', 1);

        // Count all Message
        $allMessageCount = UserMessage::model()->countByAttributes(array('user_id' => Yii::app()->user->id));


        $sql = "SELECT user_message.*
		FROM user_message
		LEFT JOIN message on message.id = user_message.message_id
		WHERE  user_message.user_id = :userId
		ORDER BY message.updated_at DESC
		LIMIT " . intval(($page - 1) * $mailsPerPage) . "," . intval($mailsPerPage);

        $pages = new CPagination($allMessageCount);
        $pages->setPageSize($mailsPerPage);

        $userMessages = UserMessage::model()->findAllBySql($sql, array(":userId" => Yii::app()->user->id));

        $this->renderPartial('/mail/list', array(
            'userMessages' => $userMessages,
            'mailCount' => $allMessageCount,
            'pageSize' => $mailsPerPage,
            'pages' => $pages
        ));
    }

    /**
     * Shows a Message
     *
     * This method also supports reply and invite new people to the conversation.
     */
    public function actionShow()
    {

        // Load Message
        $id = (int)Yii::app()->request->getQuery('id');
        $message = $this->getMessage($id);

        if ($message == null) {
            //throw new CHttpException(404, 'Could not find message!');
            $this->renderPartial('/mail/show', array('message' => $message));
        } else {

            // Update User Message Entry
            $userMessage = UserMessage::model()->findByAttributes(array(
                'user_id' => Yii::app()->user->id,
                'message_id' => $message->id,
            ));
            $userMessage->scenario = 'last_viewed';
            $userMessage->last_viewed = new CDbExpression('NOW()');
            $userMessage->save();

            // Reply Form
            $replyForm = new ReplyMessageForm;
            if (isset($_POST['ReplyMessageForm'])) {

                $replyForm->attributes = $_POST['ReplyMessageForm'];

                if ($replyForm->validate()) {

                    // Attach Message Entry
                    $messageEntry = new MessageEntry();
                    $messageEntry->message_id = $message->id;
                    $messageEntry->user_id = Yii::app()->user->id;
                    $messageEntry->content = $this->cleanUpMessage($replyForm->message);
                    $messageEntry->save();
                    $messageEntry->notify();

                    //  Update Modified_at Value
                    $message->save();

                    $this->redirect($this->createUrl('index', array('id' => $message->id)));
                }
            }

            // Invite Form
            $inviteForm = new InviteRecipientForm;
            $inviteForm->message = $message;
            if (isset($_POST['InviteRecipientForm'])) {

                $inviteForm->attributes = $_POST['InviteRecipientForm'];

                if ($inviteForm->validate()) {

                    foreach ($inviteForm->getRecipients() as $user) {

                        // Attach User Message
                        $userMessage = new UserMessage();
                        $userMessage->message_id = $message->id;
                        $userMessage->user_id = $user->id;
                        $userMessage->is_originator = 0;
                        $userMessage->save();

                        $message->notify($user);
                    }

                    $this->redirect($this->createUrl('show', array('id' => $message->id)));
                }
            }


            $this->renderPartial('/mail/show', array('message' => $message, 'replyForm' => $replyForm, 'inviteForm' => $inviteForm));
        }
    }

    /**
     * Shows the invite user form
     *
     * This method invite new people to the conversation.
     */
    public function actionAddUser()
    {

        // Load Message
        $id = Yii::app()->request->getQuery('id');
        $message = $this->getMessage($id);

        if ($message == null) {
            throw new CHttpException(404, 'Could not find message!');
        }

        // Update User Message Entry
        $userMessage = UserMessage::model()->findByAttributes(array(
            'user_id' => Yii::app()->user->id,
            'message_id' => $message->id,
        ));
        $userMessage->scenario = 'last_viewed';
        $userMessage->last_viewed = new CDbExpression('NOW()');
        $userMessage->save();

        // Reply Form
        $replyForm = new ReplyMessageForm;
        if (isset($_POST['ReplyMessageForm'])) {

            $replyForm->attributes = $_POST['ReplyMessageForm'];

            if ($replyForm->validate()) {

                // Attach Message Entry
                $messageEntry = new MessageEntry();
                $messageEntry->message_id = $message->id;
                $messageEntry->user_id = Yii::app()->user->id;
                $messageEntry->content = $this->cleanUpMessage($replyForm->message);
                $messageEntry->save();
                $messageEntry->notify();

                //  Update Modified_at Value
                $message->save();

                // Close modal
                $this->renderModalClose();

                // refresh current page to show new added user
                $this->redirect($this->createUrl('index', array('id' => $message->id)));
            }
        }

        // Invite Form
        $inviteForm = new InviteRecipientForm;
        $inviteForm->message = $message;
        if (isset($_POST['InviteRecipientForm'])) {

            $inviteForm->attributes = $_POST['InviteRecipientForm'];

            if ($inviteForm->validate()) {

                foreach ($inviteForm->getRecipients() as $user) {

                    // Attach User Message
                    $userMessage = new UserMessage();
                    $userMessage->message_id = $message->id;
                    $userMessage->user_id = $user->id;
                    $userMessage->is_originator = 0;
                    $userMessage->save();

                    $message->notify($user);
                }

                // Refresh the page to show the changes
                $this->htmlRedirect($this->createUrl('index', array('id' => $message->id)));
            }
        }

        $output = $this->renderPartial('/mail/adduser', array('message' => $message, 'replyForm' => $replyForm, 'inviteForm' => $inviteForm));
        Yii::app()->clientScript->render($output);
        echo $output;
        Yii::app()->end();
    }

    /**
     * Creates a new Message
     * and redirects to it.
     */
    public function actionCreate()
    {

        $model = new CreateMessageForm;

        // Uncomment the following line if AJAX validation is needed
        //$this->performAjaxValidation($model);
        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'create-message-form') {
            echo CActiveForm::validate($model);
            Yii::app()->end();
        }

        if (isset($_POST['CreateMessageForm'])) {
            $model->attributes = $_POST['CreateMessageForm'];

            $title = $model->title;
            $title = Yii::app()->input->stripClean($title);

            if ($model->validate()) {

                // Create new Message
                $message = new Message();
                $message->title = $title;
                $message->save();

                // Attach Message Entry
                $messageEntry = new MessageEntry();
                $messageEntry->message_id = $message->id;
                $messageEntry->user_id = Yii::app()->user->id;
                $messageEntry->content = $this->cleanUpMessage($model->message);
                $messageEntry->save();

                // Attach also Recipients
                foreach ($model->getRecipients() as $recipient) {
                    $userMessage = new UserMessage();
                    $userMessage->message_id = $message->id;
                    $userMessage->user_id = $recipient->id;
                    $userMessage->save();
                }

                // Inform recipients (We need to add all before)
                foreach ($model->getRecipients() as $recipient) {
                    $message->notify($recipient);
                }

                // Attach User Message
                $userMessage = new UserMessage();
                $userMessage->message_id = $message->id;
                $userMessage->user_id = Yii::app()->user->id;
                $userMessage->is_originator = 1;
                $userMessage->last_viewed = new CDbExpression('NOW()');
                $userMessage->save();

                // Close modal
                //$this->renderModalClose();

                // refresh current page to show new added user
                $this->htmlRedirect($this->createUrl('index'));
            }
        }

        $output = $this->renderPartial('create', array('model' => $model));
        Yii::app()->clientScript->render($output);
        echo $output;
        Yii::app()->end();
    }

    /**
     * Leave Message / Conversation
     *
     * Leave is only possible when at least to people are in the
     * conversation.
     */
    public function actionLeave()
    {

        $id = Yii::app()->request->getQuery('id');
        $message = $this->getMessage($id);

        if ($message == null) {
            throw new CHttpException(404, 'Could not find message!');
        }

        if ($message->users < 3) {
            throw new CHttpException(500, 'Could not leave message, needs at least 2 persons!');
        }

        if ($message->originator->id == Yii::app()->user->id) {
            throw new CHttpException(500, 'Originator could not leave his message!');
        }

        $userMessage = UserMessage::model()->findByAttributes(array('message_id' => $message->id, 'user_id' => Yii::app()->user->id));
        $userMessage->leave();

        $this->redirect($this->createUrl('index'));
    }

    /**
     * Delete Entry Id
     *
     * Users can delete the own message entries.
     */
    public function actionDeleteEntry()
    {

        $messageEntryId = Yii::app()->request->getQuery('id');
        $messageEntry = MessageEntry::model()->findByPk($messageEntryId);

        // Check if message entry exists and it´s by this user
        if ($messageEntry == null || $messageEntry->user_id != Yii::app()->user->id) {
            throw new CHttpException(404, 'Could not find message entry!');
        }

        $message = $messageEntry->message;
        if ($message == null) {
            throw new CHttpException(404, 'Could not find message!');
        }

        // We are deleting the first (last) entry, so delete the whole message
        if (count($message->entries) == 1) {
            $message->delete();
            $this->redirect($this->createUrl('index'));
        } else {
            $messageEntry->delete();
            $this->redirect($this->createUrl('show', array('id' => $messageEntry->message_id)));
        }
    }

    /**
     * Returns the Message Model by given Id
     * Also an access check will be performed.
     *
     * If insufficed privileges or not found null will be returned.
     *
     * @param int $id
     */
    private function getMessage($id)
    {

        $message = Message::model()->findByAttributes(array('id' => $id));

        if ($message != null) {

            $userMessage = UserMessage::model()->findByAttributes(array('user_id' => Yii::app()->user->id, 'message_id' => $message->id));
            if ($userMessage != null) {
                return $message;
            }
        }

        return null;
    }

    /**
     * Cleans up Message Content
     *
     * @param type $msg
     * @return cleaned up
     */
    private function cleanUpMessage($msg)
    {

        $p = new CHtmlPurifier();
        $p->options = array('URI.AllowedSchemes' => array(
            'http' => true,
            'https' => true,
        ));
        $msg = $p->purify($msg);


        //$msg = CHtml::encode($msg);
        //$msg = nl2br($msg);
        return $msg;
    }


    /**
     * Returns a JSON Object which contains a lot of informations about
     * current states like new posts on workspaces
     */
    public function actionGetMessageCount()
    {

        $json = array();

        // New message count
        $sql = "SELECT count(message_id)
                FROM user_message
                LEFT JOIN message on message.id = user_message.message_id
                WHERE  user_message.user_id = :user_id AND (message.updated_at >  user_message.last_viewed OR user_message.last_viewed IS NULL)";
        $connection = Yii::app()->db;
        $command = $connection->createCommand($sql);
        $userId = Yii::app()->user->id;
        $command->bindParam(":user_id", $userId);
        $json['newMessages'] = $command->queryScalar();


        print CJSON::encode($json);
        Yii::app()->end();
    }

}