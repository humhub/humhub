<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\widgets;

use Yii;
use humhub\modules\ui\menu\MenuLink;
use humhub\modules\space\models\Membership;
use humhub\modules\space\modules\manage\models\MembershipSearch;
use humhub\modules\ui\menu\widgets\TabMenu;

/**
 * MemberMenu is a tabbed menu for space member administration
 *
 * @author Basti
 */
class MemberMenu extends TabMenu
{
    /**
     * @var \humhub\modules\space\models\Space
     */
    public $space;

    /**
     * @inheritdoc
     */
    public function init()
    {

        $this->addEntry(new MenuLink([
            'label' => Yii::t('SpaceModule.manage', 'Members'),
            'url' => $this->space->createUrl('/space/manage/member/index'),
            'sortOrder' => 100,
            'isActive' => MenuLink::isActiveState(null, 'member', 'index')
        ]));

        if ($this->countPendingInvites() != 0) {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('SpaceModule.manage', 'Pending Invites') . '&nbsp;&nbsp;<span class="label label-danger">' . $this->countPendingInvites() . '</span>',
                'url' => $this->space->createUrl('/space/manage/member/pending-invitations'),
                'sortOrder' => 200,
                'isActive' => MenuLink::isActiveState(null, 'member', 'pending-invitations')
            ]));
        }
        if ($this->countPendingApprovals() != 0) {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('SpaceModule.manage', 'Pending Approvals') . '&nbsp;&nbsp;<span class="label label-danger">' . $this->countPendingApprovals() . '</span>',
                'url' => $this->space->createUrl('/space/manage/member/pending-approvals'),
                'sortOrder' => 300,
                'isActive' => MenuLink::isActiveState(null, 'member', 'pending-approvals')
            ]));
        }

        if ($this->space->isSpaceOwner()) {
            $this->addEntry(new MenuLink([
                'label' => Yii::t('SpaceModule.manage', 'Owner'),
                'url' => $this->space->createUrl('/space/manage/member/change-owner'),
                'sortOrder' => 500,
                'isActive' => MenuLink::isActiveState(null, 'member', 'change-owner')
            ]));
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
