<?php
namespace humhub\modules\space\widgets;

use Yii;

/**
 * Description of InviteModal
 *
 * @author buddha
 */
class InviteModal extends \yii\base\Widget
{
    public $submitText;
    public $submitAction;
    public $model;
    public $attribute;
    public $searchUrl;
    
    public function run()
    {
        if(!$this->attribute) {
            $this->attribute = 'invite';
        }
        
        return $this->render('inviteModal', [
            'canInviteExternal' => Yii::$app->getModule('user')->settings->get('auth.internalUsersCanInvite'),
            'submitText' => $this->submitText,
            'submitAction' => $this->submitAction,
            'model' => $this->model,
            'attribute' => $this->attribute,
            'searchUrl' => $this->searchUrl
        ]);
    }
}
