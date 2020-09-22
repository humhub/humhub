<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\controllers;

use humhub\modules\content\components\ContentContainerControllerAccess;
use Yii;
use yii\web\HttpException;
use humhub\modules\space\models\Space;
use humhub\modules\space\modules\manage\components\Controller;
use humhub\modules\space\modules\manage\models\MembershipSearch;
use humhub\modules\space\notifications\ChangedRolesMembership;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Membership;
use humhub\modules\space\modules\manage\models\ChangeOwnerForm;

/**
 * Member Controller
 *
 * @author Luke
 */
class MemberController extends Controller
{
    /**
     * @inheritdoc
     */
    protected function getAccessRules() {
        return [
            ['login'],
            [ContentContainerControllerAccess::RULE_USER_GROUP_ONLY => [Space::USERGROUP_ADMIN], 'actions' => [
                'index', 'pending-invitations', 'pending-approvals', 'reject-applicant', 'approve-applicant', 'remove']],
            [ContentContainerControllerAccess::RULE_USER_GROUP_ONLY => [Space::USERGROUP_OWNER], 'actions' => ['change-owner']]
        ];
    }

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
                throw new HttpException(404, 'Could not find membership!');
            }

            if ($membership->load(Yii::$app->request->post()) && $membership->save()) {

                ChangedRolesMembership::instance()
                    ->about($membership)
                    ->from(Yii::$app->user->identity)
                    ->send($membership->user);

                return Yii::$app->request->post();
            }

            return $membership->getErrors();
        }

        return $this->render('index', [
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel,
                    'space' => $space
        ]);
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

        return $this->render('pending-invitations', [
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel,
                    'space' => $space
        ]);
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

        return $this->render('pending-approvals', [
                    'dataProvider' => $dataProvider,
                    'searchModel' => $searchModel,
                    'space' => $space
        ]);
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
        $user = User::findOne(['guid' => $userGuid]);

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
        $space = $this->getSpace();

        $model = new ChangeOwnerForm([
            'space' => $space,
            'ownerId' => $space->getSpaceOwner()->id
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $space->setSpaceOwner($model->ownerId);
            return $this->redirect($space->getUrl());
        }

        return $this->render('change-owner', [
                    'space' => $space,
                    'model' => $model
        ]);
    }

}
