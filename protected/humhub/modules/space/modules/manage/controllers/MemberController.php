<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\controllers;

use Yii;
use yii\helpers\Url;
use humhub\modules\space\modules\manage\components\Controller;
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
        $membersPerPage = 20;
        $space = $this->getSpace();

        // User Role Management
        if (isset($_POST['users'])) {
            $users = Yii::$app->request->post('users');

            // Loop over all users in Form
            foreach ($users as $userGuid) {
                // Get informations
                if (isset($_POST['user_' . $userGuid])) {
                    $userSettings = Yii::$app->request->post('user_' . $userGuid);

                    $user = User::findOne(['guid' => $userGuid]);
                    if ($user != null) {

                        // No changes on the Owner
                        if ($space->isSpaceOwner($user->id))
                            continue;

                        $membership = \humhub\modules\space\models\Membership::findOne(['user_id' => $user->id, 'space_id' => $space->id]);
                        if ($membership != null) {
                            $membership->invite_role = (isset($userSettings['inviteRole']) && $userSettings['inviteRole'] == 1) ? 1 : 0;
                            $membership->admin_role = (isset($userSettings['adminRole']) && $userSettings['adminRole'] == 1) ? 1 : 0;
                            $membership->share_role = (isset($userSettings['shareRole']) && $userSettings['shareRole'] == 1) ? 1 : 0;
                            $membership->save();
                        }
                    }
                }
            }

            // Change owner if changed
            if ($space->isSpaceOwner()) {
                $owner = $space->getSpaceOwner();
                $newOwnerId = Yii::$app->request->post('ownerId');

                if ($newOwnerId != $owner->id) {
                    if ($space->isMember($newOwnerId)) {
                        $space->setSpaceOwner($newOwnerId);

                        // Redirect to current space
                        return $this->redirect($space->createUrl('admin/manage/member'));
                    }
                }
            }

            Yii::$app->getSession()->setFlash('data-saved', Yii::t('SpaceModule.controllers_AdminController', 'Saved'));
        } // Updated Users

        $query = $space->getMemberships();
        #$query = Membership::find();
        // Allow User Searches
        $search = Yii::$app->request->post('search');
        if ($search != "") {
            $query->joinWith('user');
            $query->andWhere('user.username LIKE :search OR user.email LIKE :search', [':search' => '%' . $search . '%']);
        }

        $countQuery = clone $query;
        $pagination = new \yii\data\Pagination(['totalCount' => $countQuery->count(), 'pageSize' => $membersPerPage]);
        $query->offset($pagination->offset)->limit($pagination->limit);

        $invitedMembers = Membership::findAll(['space_id' => $space->id, 'status' => Membership::STATUS_INVITED]);

        $members = $query->all();

        return $this->render('index', array(
                    'space' => $space,
                    'pagination' => $pagination,
                    'members' => $members,
                    'invited_members' => $invitedMembers,
                    'search' => $search,
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

}

?>
