<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\controllers;

use Yii;


use yii\web\HttpException;

use humhub\modules\space\modules\manage\components\Controller;
use humhub\modules\space\modules\manage\models\MembershipSearch;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Membership;


/**
 * Member Controller
 *
 * @author Luke
 */
class MemberController extends Controller
{

    /**
     * Members Administration Action
     */
    public function actionIndex()
    {
        $space = $this->getSpace();
        $searchModel = new MembershipSearch();
        $searchModel->space_id = $space->id;
        $searchModel->status = Membership::STATUS_MEMBER;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        // User Group Change
        if (Yii::$app->request->post('dropDownColumnSubmit')) {
            Yii::$app->response->format = 'json';
            $membership = Membership::findOne(['space_id' => $space->id, 'user_id' => Yii::$app->request->post('user_id')]);
            if ($membership === null) {
                throw new \yii\web\HttpException(404, 'Could not find membership!');
            }

            if ($membership->load(Yii::$app->request->post()) && $membership->validate() && $membership->save()) {
                return Yii::$app->request->post();
            }
            return $membership->getErrors();
        }

        return $this->render('index', array(
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel,
                    'space' => $space
        ));
    }

    /**
     * Members Administration Action
     */
    public function actionPendingInvitations()
    {
        $space = $this->getSpace();
        $searchModel = new MembershipSearch();
        $searchModel->space_id = $space->id;
        $searchModel->status = Membership::STATUS_INVITED;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('pending-invitations', array(
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel,
                    'space' => $space
        ));
    }

    /**
     * Members Administration Action
     */
    public function actionPendingApprovals()
    {
        $space = $this->getSpace();
        $searchModel = new MembershipSearch();
        $searchModel->space_id = $space->id;
        $searchModel->status = Membership::STATUS_APPLICANT;
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('pending-approvals', array(
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel,
                    'space' => $space
        ));
    }

    /**
     * User Manage Users Page, Reject Member Request Link
     */
    public function actionRejectApplicant()
    {
        $this->forcePostRequest();

        $space = $this->getSpace();
        $userGuid = Yii::$app->request->get('userGuid');
        $user = User::findOne(['guid' => $userGuid]);

        if ($user != null) {
            $space->removeMember($user->id);
        }

        return $this->redirect($space->getUrl());
    }

    /**
     * User Manage Users Page, Approve Member Request Link
     */
    public function actionApproveApplicant()
    {
        $this->forcePostRequest();

        $space = $this->getSpace();
        $userGuid = Yii::$app->request->get('userGuid');
        $user = User::findOne(['guid' => $userGuid]);

        if ($user != null) {
            $membership = $space->getMembership($user->id);
            if ($membership != null && $membership->status == Membership::STATUS_APPLICANT) {
                $space->addMember($user->id);
            }
        }

        return $this->redirect($space->getUrl());
    }

    /**
     * Removes a Member
     */
    public function actionRemove()
    {
        $this->forcePostRequest();

        $space = $this->getSpace();
        $userGuid = Yii::$app->request->get('userGuid');
        $user = User::findOne(array('guid' => $userGuid));

        if ($space->isSpaceOwner($user->id)) {
            throw new HttpException(500, 'Owner cannot be removed!');
        }

        $space->removeMember($user->id);

        // Redirect  back to Administration page
        return $this->htmlRedirect($space->createUrl('/space/manage/member'));
    }

    /**
     * Change owner
     */
    public function actionChangeOwner()
    {
        $this->ownerOnly();
        $space = $this->getSpace();

        $model = new \humhub\modules\space\modules\manage\models\ChangeOwnerForm();
        $model->ownerId = $space->getSpaceOwner()->id;

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $space->setSpaceOwner($model->ownerId);
            return $this->redirect($space->getUrl());
        }

        return $this->render('change-owner', array(
                    'space' => $space,
                    'model' => $model
        ));
    }

}

?>
