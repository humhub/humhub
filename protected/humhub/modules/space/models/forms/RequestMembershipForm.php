<?php

namespace humhub\modules\space\models\forms;

use humhub\modules\space\widgets\MembershipButton;
use Yii;
use yii\base\Model;

/**
 * @author Luke
 * @package humhub.modules_core.space.forms
 * @since 0.5
 */
class RequestMembershipForm extends Model
{
    public $space_id;
    public $message;

    /**
     * @var string Name of the membership button variant the request originated from, so the
     * button can be re-rendered in the same presentation. Unknown names resolve to the
     * default variant.
     * @since 1.19
     */
    public $variant = MembershipButton::VARIANT_DEFAULT;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return [
            ['message', 'required'],
            ['variant', 'filter', 'filter' => [MembershipButton::class, 'resolveVariant']],
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
