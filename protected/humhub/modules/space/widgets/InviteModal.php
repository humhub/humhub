<?php

namespace humhub\modules\space\widgets;

use humhub\modules\admin\permissions\ManageUsers;
use humhub\modules\space\models\forms\InviteForm;
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
     * @var InviteForm
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
            'canInviteByEmail' => $this->model->canInviteByEmail(),
            'canInviteByLink' => $this->model->canInviteByLink(),
            'submitText' => $this->submitText,
            'submitAction' => $this->submitAction,
            'model' => $this->model,
            'attribute' => $this->attribute,
            'searchUrl' => $this->searchUrl,
            'canAddWithoutInvite' => Yii::$app->user->can(ManageUsers::class) || Yii::$app->getModule('space')->membersCanAddWithoutInvite === true,
        ]);
    }
}
