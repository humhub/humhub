<?php

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.10.0
 */
class PrivacySettingsForm extends CFormModel {

    public $defaultDisplayProfileFollowerInfo;
    public $allowUserOverrideFollowerSetting;
    
    public $defaultDisplayProfileFollowingInfo;
    public $allowUserOverrideFollowingSetting;
    
    public $defaultDisplayProfileSpaceInfo;
    public $allowUserOverrideSpaceSetting;
    
    /**
     * Declares the validation rules.
     */
    public function rules() {
        return array(
        	array('defaultDisplayProfileFollowerInfo, defaultDisplayProfileFollowingInfo, defaultDisplayProfileSpaceInfo', 'in', 'range'=>array('open', 'hide')),
        	array('allowUserOverrideFollowerSetting, allowUserOverrideFollowingSetting, allowUserOverrideSpaceSetting', 'boolean'),
        	array('defaultDisplayProfileFollowerInfo, defaultDisplayProfileFollowingInfo, defaultDisplayProfileSpaceInfo, allowUserOverrideFollowerSetting, allowUserOverrideFollowingSetting, allowUserOverrideSpaceSetting', 'length', 'max' => 255)
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels() {
        return array(
            'defaultDisplayProfileFollowerInfo' => Yii::t('AdminModule.forms_PrivacySettingsForm', 'Display "Follower" Information on User Profile'),
            'allowUserOverrideFollowerSetting' => Yii::t('AdminModule.forms_PrivacySettingsForm', 'Allow user to override this setting'),
        	'defaultDisplayProfileFollowingInfo' => Yii::t('AdminModule.forms_PrivacySettingsForm', 'Display "Following" Information on User Profile'),
        	'allowUserOverrideFollowingSetting' => Yii::t('AdminModule.forms_PrivacySettingsForm', 'Allow user to override this setting'),
        	'defaultDisplayProfileSpaceInfo' => Yii::t('AdminModule.forms_PrivacySettingsForm', 'Display "Space" Information on User Profile'),
        	'allowUserOverrideSpaceSetting' => Yii::t('AdminModule.forms_PrivacySettingsForm', 'Allow user to override this setting'),
        );
    }

}
