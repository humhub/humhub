<?php

namespace humhub\modules\space\models\forms;

use Yii;
use yii\base\Model;

/**
 * The message a user introduces themselves with when applying for membership in a space that
 * approves memberships.
 *
 * Since 1.20 the form is collected by the `MembershipButton` island and validated by the
 * membership API (`space\controllers\api\MembershipController::actionAffirm()`) — this model
 * stays the single place that says the message is required.
 *
 * @author Luke
 * @package humhub.modules_core.space.forms
 * @since 0.5
 */
class RequestMembershipForm extends Model
{
    public $space_id;
    public $message;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return [
            ['message', 'required'],
        ];
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return [
            'message' => Yii::t('SpaceModule.base', 'Your Message'),
        ];
    }

}
