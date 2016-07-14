<?php

namespace humhub\modules\admin\models\forms;

use Yii;

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.5
 */
class BasicSettingsForm extends \yii\base\Model
{

    public $name;
    public $baseUrl;
    public $defaultLanguage;
    public $defaultSpaceGuid;
    public $tour;
    public $share;
    public $timeZone;
    public $dashboardShowProfilePostForm;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            array(['name', 'baseUrl'], 'required'),
            array('name', 'string', 'max' => 150),
            array('defaultLanguage', 'in', 'range' => array_keys(Yii::$app->params['availableLanguages'])),
            array('timeZone', 'in', 'range' => \DateTimeZone::listIdentifiers()),
            array('defaultSpaceGuid', 'checkSpaceGuid'),
            array(['tour', 'share', 'dashboardShowProfilePostForm'], 'in', 'range' => array(0, 1))
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'name' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Name of the application'),
            'baseUrl' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Base URL'),
            'defaultLanguage' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Default language'),
            'timeZone' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Server Timezone'),
            'defaultSpaceGuid' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Default space'),
            'tour' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Show introduction tour for new users'),
            'share' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Show sharing panel on dashboard'),
            'dashboardShowProfilePostForm' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Show user profile post form on dashboard')
        );
    }

    /**
     * This validator function checks the defaultSpaceGuid.
     *
     * @param type $attribute
     * @param type $params
     */
    public function checkSpaceGuid($attribute, $params)
    {

        if ($this->defaultSpaceGuid != "") {

            foreach (explode(',', $this->defaultSpaceGuid) as $spaceGuid) {
                if ($spaceGuid != "") {
                    $space = \humhub\modules\space\models\Space::findOne(array('guid' => $spaceGuid));
                    if ($space == null) {
                        $this->addError($attribute, Yii::t('AdminModule.forms_BasicSettingsForm', "Invalid space"));
                    }
                }
            }
        }
    }

}
