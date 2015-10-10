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
            'label' => 'Members',
            'url' => $this->space->createUrl('index'),
            'sortOrder' => 100,
            'isActive' => (Yii::$app->controller->action->id == 'index'),
        ));
        $this->addItem(array(
            'label' => 'Pending Invites',
            'url' => $this->space->createUrl('pending-invitations'),
            'sortOrder' => 200,
            'isActive' => (Yii::$app->controller->action->id == 'pending-invitations'),
        ));
        $this->addItem(array(
            'label' => 'Pending Approvals',
            'url' => $this->space->createUrl('pending-approvals'),
            'sortOrder' => 300,
            'isActive' => (Yii::$app->controller->action->id == 'pending-approvals'),
        ));
        $this->addItem(array(
            'label' => 'Permissions',
            'url' => $this->space->createUrl('permissions'),
            'sortOrder' => 400,
            'isActive' => (Yii::$app->controller->action->id == 'permissions'),
        ));

        if ($this->space->isSpaceOwner()) {
            $this->addItem(array(
                'label' => 'Owner',
                'url' => $this->space->createUrl('change-owner'),
                'sortOrder' => 500,
                'isActive' => (Yii::$app->controller->action->id == 'change-owner'),
            ));
        }


        parent::init();
    }

}
