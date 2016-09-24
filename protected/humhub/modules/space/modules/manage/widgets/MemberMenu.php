<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\widgets;

use Yii;
use humhub\modules\space\modules\manage\models\MembershipSearch;
use humhub\modules\space\models\Membership;

/**
 * MemberMenu is a tabbed menu for space member administration
 *
 * @author Basti
 */
class MemberMenu extends \humhub\widgets\BaseMenu
{

    /**
     * @inheritdoc
     */
    public $template = "@humhub/widgets/views/tabMenu";

    /**
     * @var \humhub\modules\space\models\Space
     */
    public $space;

    /**
     * @inheritdoc
     */
    public function init()
    {

        $this->addItem(array(
            'label' => Yii::t('SpaceModule.widgets_SpaceMembersMenu', 'Members'),
            'url' => $this->space->createUrl('/space/manage/member/index'),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->action->id == 'index' && Yii::$app->controller->id === 'member'),
        ));

        if ($this->countPendingInvites() != 0) {
            $this->addItem(array(
                'label' => Yii::t('SpaceModule.widgets_SpaceMembersMenu', 'Pending Invites') . '&nbsp;&nbsp;<span class="label label-danger">'.$this->countPendingInvites().'</span>',
                'url' => $this->space->createUrl('/space/manage/member/pending-invitations'),
                'sortOrder' => 200,
                'isActive' => (Yii::$app->controller->action->id == 'pending-invitations'),
            ));
        }
        if ($this->countPendingApprovals() != 0) {
            $this->addItem(array(
                'label' => Yii::t('SpaceModule.widgets_SpaceMembersMenu', 'Pending Approvals'). '&nbsp;&nbsp;<span class="label label-danger">'.$this->countPendingApprovals().'</span>',
                'url' => $this->space->createUrl('/space/manage/member/pending-approvals'),
                'sortOrder' => 300,
                'isActive' => (Yii::$app->controller->action->id == 'pending-approvals'),
            ));
        }

        if ($this->space->isSpaceOwner()) {
            $this->addItem(array(
                'label' => Yii::t('SpaceModule.widgets_SpaceMembersMenu', 'Owner'),
                'url' => $this->space->createUrl('/space/manage/member/change-owner'),
                'sortOrder' => 500,
                'isActive' => (Yii::$app->controller->action->id == 'change-owner'),
            ));
        }


        parent::init();
    }

    /**
     * Returns the number of currently invited users
     * 
     * @return int currently invited members
     */
    protected function countPendingInvites()
    {
        $searchModel = new MembershipSearch();
        $searchModel->space_id = $this->space->id;
        $searchModel->status = Membership::STATUS_INVITED;
        return $searchModel->search([])->getCount();
    }

    /**
     * Returns the number of currently pending approvals
     * 
     * @return int currently pending approvals
     */
    protected function countPendingApprovals()
    {
        $searchModel = new MembershipSearch();
        $searchModel->space_id = $this->space->id;
        $searchModel->status = Membership::STATUS_APPLICANT;
        return $searchModel->search([])->getCount();
    }

}
