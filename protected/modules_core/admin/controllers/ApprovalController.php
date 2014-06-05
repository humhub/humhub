<?php

/**
 * @package humhub.modules_core.admin.controllers
 * @since 0.5
 */
class ApprovalController extends Controller {

    public $subLayout = "/_layout";

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
            array('allow',
                'expression' => 'Yii::app()->user->canApproveUsers()'
            ),
            array('deny', // deny all users
                'users' => array('*'),
            ),
        );
    }

    /**
     * Shows a list of all users waiting for an approval
     */
    public function actionIndex() {

        $model = new User('search');
        if (isset($_GET['User']))
            $model->attributes = $_GET['User'];

        $model->status = User::STATUS_NEED_APPROVAL;

        $this->render('index', array(
            'model' => $model
        ));
    }

    /**
     * Approves a user registration request
     *
     * @throws CHttpException
     */
    public function actionApproveUserAccept() {

        $id = (int) Yii::app()->request->getQuery('id');

        $model = User::model()->resetScope()->unapproved()->findByPk($id);

        if ($model == null)
            throw new CHttpException(404, Yii::t('base', 'User not found!'));

        $approveFormModel = new ApproveUserForm;

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'approve-acceptuser-form') {
            echo CActiveForm::validate($approveFormModel);
            Yii::app()->end();
        }

        if (isset($_POST['ApproveUserForm'])) {

            $approveFormModel->attributes = $_POST['ApproveUserForm'];

            if ($approveFormModel->validate()) {
                $approveFormModel->send($model->email);
                $model->status = User::STATUS_ENABLED;
                $model->save();
                $this->redirect(Yii::app()->createUrl('admin/approval'));
            }
        } else {
            $approveFormModel->subject = "Account Request for '" . $model->displayName . "' has been approved.";
            $approveFormModel->message = "Hello {$model->displayName},<br><br>\n\n" .
                    "your account has been activated.<br><br>\n" .
                    "Click here to login:<br>\n" .
                    "<a href='" . Yii::app()->createAbsoluteUrl("//user/auth/login") . "'>" . Yii::app()->createAbsoluteUrl("//user/auth/login") . "</a><br><br>\n" .
                    "Kind Regards<br>\n" .
                    Yii::app()->user->model->displayName . "<br><br>\n\n";
        }


        $this->render('approveUserAccept', array('model' => $model, 'approveFormModel' => $approveFormModel));
    }

    /**
     * Declines a user registration request
     *
     * @throws CHttpException
     */
    public function actionApproveUserDecline() {

        $id = (int) Yii::app()->request->getQuery('id');
        $user = User::model()->resetScope()->unapproved()->findByPk($id);

        if ($user == null)
            throw new CHttpException(404, Yii::t('AdminModule.approval', 'User not found!'));

        $approveFormModel = new ApproveUserForm;

        // uncomment the following code to enable ajax-based validation
        if (isset($_POST['ajax']) && $_POST['ajax'] === 'approve-declineuser-form') {
            echo CActiveForm::validate($approveFormModel);
            Yii::app()->end();
        }

        if (isset($_POST['ApproveUserForm'])) {

            $approveFormModel->attributes = $_POST['ApproveUserForm'];

            if ($approveFormModel->validate()) {
                $approveFormModel->send($user->email);
                $user->delete();
                $this->redirect(Yii::app()->createUrl('admin/approval'));
            }
        } else {
            $approveFormModel->subject = "Account Request for '" . $user->displayName . "' has been declined.";
            $approveFormModel->message = "Hello {$user->displayName} ,<br><br>\n\n" .
                    "your account request has been declined.<br><br>\n\n" .
                    "Kind Regards<br>\n" .
                    Yii::app()->user->model->displayName . "<br><br>";
        }

        $this->render('approveUserDecline', array('model' => $user, 'approveFormModel' => $approveFormModel));
    }

}

?>
