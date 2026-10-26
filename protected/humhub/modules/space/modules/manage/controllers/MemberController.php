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
use humhub\modules\space\notifications\SpaceRolesChangedNotification;
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
    protected function getAccessRules()
    {
        return [
            ['login'],
            [ContentContainerControllerAccess::RULE_USER_GROUP_ONLY => [Space::USERGROUP_ADMIN], 'actions' => [
                'index',
                'pending-invitations',
                'pending-approvals',
                'reject-applicant',
                'approve-applicant',
                'remove',
            ]],
            [ContentContainerControllerAccess::RULE_USER_GROUP_ONLY => [Space::USERGROUP_OWNER], 'actions' => ['change-owner']],
        ];
    }

    /**
     * The members of the space (`manage\widgets\SpaceMemberTable`, its rows from
     * `GET /api/v2/space/<id>/member`)
     */
    public function actionIndex()
    {
        $space = $this->getSpace();

        // User Group Change (the role select of the former member list)
        if (Yii::$app->request->post('dropDownColumnSubmit')) {
            Yii::$app->response->format = 'json';
            $membership = Membership::findMembership($space->id, Yii::$app->request->post('user_id'));
            if ($membership === null) {
                throw new HttpException(404, 'Could not find membership!');
            }

            // The owner role follows the space creator and is only changeable via change-owner.
            if ($space->isSpaceOwner($membership->user_id)) {
                throw new HttpException(403, 'The role of the space owner cannot be changed!');
            }

            // Restrict this endpoint to the role itself, see Membership::scenarios().
            $membership->scenario = Membership::SCENARIO_EDIT_ROLE;

            if ($membership->load(Yii::$app->request->post()) && $membership->save()) {

                SpaceRolesChangedNotification::send(
                    $membership->user,
                    $membership,
                    Yii::$app->user->identity,
                    dedupe: false,
                    payload: ['groupId' => $membership->group_id],
                );

                return Yii::$app->request->post();
            }

            return $membership->getErrors();
        }

        return $this->render('index', ['space' => $space]);
    }

    /**
     * Members Administration Action
     */
    public function actionPendingInvitations()
    {
        return $this->render('pending-invitations', ['space' => $this->getSpace()]);
    }

    /**
     * Members Administration Action
     */
    public function actionPendingApprovals()
    {
        return $this->render('pending-approvals', ['space' => $this->getSpace()]);
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
            'ownerId' => $space->getSpaceOwner()->id,
        ]);

        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            $space->setSpaceOwner($model->ownerId);
            return $this->redirect($space->getUrl());
        }

        return $this->render('change-owner', [
            'space' => $space,
            'model' => $model,
        ]);
    }
}
