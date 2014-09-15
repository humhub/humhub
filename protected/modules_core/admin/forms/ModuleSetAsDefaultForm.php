<?php

/**
 * GroupForm is used to modify group settings
 *
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class ModuleSetAsDefaultForm extends CFormModel
{

    public $spaceDefaultState;
    public $userDefaultState;

    /**
     * Validation rules for group form
     *
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return array(
            array('userDefaultState, spaceDefaultState', 'numerical', 'integerOnly' => true),
        );
    }

}
