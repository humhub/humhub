<?php

/**
 * @author Luke
 * @package humhub.modules_core.space.forms
 * @since 0.5
 */
class SpaceNotificationForm extends CFormModel {

    public $strength;
    public $scope;

    /**
     * Declares the validation rules.
     * The rules state that username and password are required,
     * and password needs to be authenticated.
     */
    public function rules() {
        return array(
            array('strength, scope', 'safe'),
        );
    }

    /**
     * Declares attribute labels.
     */
    public function attributeLabels() {
        return array(
            'strength' => Yii::t('SpaceModule.forms_SpaceNotificationForm', 'Strength'),
            'scope' => Yii::t('SpaceModule.forms_SpaceNotificationForm', 'Scope'),
        );
    }

}
