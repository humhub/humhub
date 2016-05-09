<?php

namespace humhub\modules\admin\models\forms;

use Yii;
use humhub\modules\space\models\Space;
use humhub\libs\DynamicConfig;

/**
 * BasicSettingsForm
 * 
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
    public $enableFriendshipModule;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->name = Yii::$app->settings->get('name');
        $this->baseUrl = Yii::$app->settings->get('baseUrl');
        $this->defaultLanguage = Yii::$app->settings->get('defaultLanguage');
        $this->timeZone = Yii::$app->settings->get('timeZone');

        $this->share = Yii::$app->getModule('dashboard')->settings->get('share.enable');
        $this->dashboardShowProfilePostForm = Yii::$app->getModule('dashboard')->settings->get('showProfilePostForm');
        $this->tour = Yii::$app->getModule('tour')->settings->get('enable');
        $this->enableFriendshipModule = Yii::$app->getModule('friendship')->settings->get('enable');

        $this->defaultSpaceGuid = "";
        foreach (\humhub\modules\space\models\Space::findAll(['auto_add_new_members' => 1]) as $defaultSpace) {
            $this->defaultSpaceGuid .= $defaultSpace->guid . ",";
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array(
            array(['name', 'baseUrl'], 'required'),
            array('name', 'string', 'max' => 150),
            array('defaultLanguage', 'in', 'range' => array_keys(Yii::$app->i18n->getAllowedLanguages())),
            array('timeZone', 'in', 'range' => \DateTimeZone::listIdentifiers()),
            array('defaultSpaceGuid', 'checkSpaceGuid'),
            array(['tour', 'share', 'dashboardShowProfilePostForm', 'enableFriendshipModule'], 'in', 'range' => array(0, 1))
        );
    }

    /**
     * @inheritdoc
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
            'dashboardShowProfilePostForm' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Show user profile post form on dashboard'),
            'enableFriendshipModule' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Enable user friendship system'),
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

    /**
     * Saves the form
     * 
     * @return boolean
     */
    public function save()
    {
        Yii::$app->settings->set('name', $this->name);
        Yii::$app->settings->set('baseUrl', $this->baseUrl);
        Yii::$app->settings->set('defaultLanguage', $this->defaultLanguage);
        Yii::$app->settings->set('timeZone', $this->timeZone);

        Yii::$app->getModule('dashboard')->settings->set('showProfilePostForm', $this->dashboardShowProfilePostForm);
        Yii::$app->getModule('tour')->settings->set('enable', $this->tour);
        Yii::$app->getModule('dashboard')->settings->set('share.enable', $this->share);
        Yii::$app->getModule('friendship')->settings->set('enable', $this->enableFriendshipModule);

        $spaceGuids = explode(",", $this->defaultSpaceGuid);

        // Remove Old Default Spaces
        foreach (Space::findAll(['auto_add_new_members' => 1]) as $space) {
            if (!in_array($space->guid, $spaceGuids)) {
                $space->auto_add_new_members = 0;
                $space->save();
            }
        }

        // Add new Default Spaces
        foreach ($spaceGuids as $spaceGuid) {
            $space = Space::findOne(['guid' => $spaceGuid]);
            if ($space != null && $space->auto_add_new_members != 1) {
                $space->auto_add_new_members = 1;
                $space->save();
            }
        }
        DynamicConfig::rewrite();

        return true;
    }

}
