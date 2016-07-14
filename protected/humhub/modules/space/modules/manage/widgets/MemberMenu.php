<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\modules\manage\widgets;

use Yii;
use yii\helpers\Url;

/**
 * Description of UserMenu
 *
 * @author Basti
 */
class MemberMenu extends \humhub\widgets\BaseMenu
{

    public $template = "@humhub/widgets/views/tabMenu";

    /**
     * @var \humhub\modules\space\models\Space
     */
    public $space;

    public function init()
    {

        $this->addItem(array(
            'label' => Yii::t('SpaceModule.widgets_SpaceMembersMenu','Members'),
            'url' => $this->space->createUrl('/space/manage/member/index'),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->action->id == 'index' && Yii::$app->controller->id === 'member'),
        ));
        $this->addItem(array(
            'label' => Yii::t('SpaceModule.widgets_SpaceMembersMenu','Pending Invites'),
            'url' => $this->space->createUrl('/space/manage/member/pending-invitations'),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->action->id == 'pending-invitations'),
        ));
        $this->addItem(array(
            'label' => Yii::t('SpaceModule.widgets_SpaceMembersMenu','Pending Approvals'),
            'url' => $this->space->createUrl('/space/manage/member/pending-approvals'),
            'sortOrder' => 300,
            'isActive' => (Yii::$app->controller->action->id == 'pending-approvals'),
        ));
        $this->addItem(array(
            'label' => Yii::t('SpaceModule.widgets_SpaceMembersMenu','Permissions'),
            'url' => $this->space->createUrl('/space/manage/member/permissions'),
            'sortOrder' => 400,
            'isActive' => (Yii::$app->controller->action->id == 'permissions'),
        ));

        if ($this->space->isSpaceOwner()) {
            $this->addItem(array(
                'label' => Yii::t('SpaceModule.widgets_SpaceMembersMenu','Owner'),
                'url' => $this->space->createUrl('/space/manage/member/change-owner'),
                'sortOrder' => 500,
                'isActive' => (Yii::$app->controller->action->id == 'change-owner'),
            ));
        }


        parent::init();
    }

}
