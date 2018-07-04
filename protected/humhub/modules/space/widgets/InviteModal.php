<?php

namespace humhub\modules\space\widgets;

use humhub\modules\admin\permissions\ManageUsers;
use Yii;
use yii\base\Widget;

/**
 * Description of InviteModal
 *
 * @author buddha
 */
class InviteModal extends Widget
{
    public $submitText;
    public $submitAction;

    /**
     * @var \humhub\modules\space\models\forms\InviteForm
     */
    public $model;

    public $attribute;
    public $searchUrl;

    public function run()
    {
        if (!$this->attribute) {
            $this->attribute = 'invite';
        }

        return $this->render('inviteModal', [
            'canInviteExternal' => Yii::$app->getModule('user')->settings->get('auth.internalUsersCanInvite'),
            'submitText' => $this->submitText,
            'submitAction' => $this->submitAction,
            'model' => $this->model,
            'attribute' => $this->attribute,
            'searchUrl' => $this->searchUrl,
            'canManageMembers' => Yii::$app->user->can(ManageUsers::class),
        ]);
    }
}
