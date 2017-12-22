<?php

namespace humhub\modules\admin\models\forms;

use Yii;
use humhub\libs\DynamicConfig;
use humhub\modules\space\models\Space;
use humhub\modules\stream\actions\Stream;

/**
 * BasicSettingsForm
 * @since 0.5
 */
class BasicSettingsForm extends \yii\base\Model
{

    public $name;
    public $baseUrl;
    public $defaultLanguage;
    public $defaultSpaceGuid = [];
    public $defaultSpaces;
    public $tour;
    public $timeZone;
    public $defaultStreamSort;
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

        $this->dashboardShowProfilePostForm = Yii::$app->getModule('dashboard')->settings->get('showProfilePostForm');
        $this->tour = Yii::$app->getModule('tour')->settings->get('enable');
        $this->enableFriendshipModule = Yii::$app->getModule('friendship')->settings->get('enable');

        $this->defaultSpaces = \humhub\modules\space\models\Space::findAll(['auto_add_new_members' => 1]);

        $this->defaultStreamSort = Yii::$app->getModule('stream')->settings->get('defaultSort');
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'baseUrl'], 'required'],
            ['name', 'string', 'max' => 150],
            ['defaultLanguage', 'in', 'range' => array_keys(Yii::$app->i18n->getAllowedLanguages())],
            ['timeZone', 'in', 'range' => \DateTimeZone::listIdentifiers()],
            ['defaultSpaceGuid', 'checkSpaceGuid'],
            [['tour', 'dashboardShowProfilePostForm', 'enableFriendshipModule'], 'in', 'range' => [0, 1]],
            [['defaultStreamSort'], 'in', 'range' => array_keys($this->getDefaultStreamSortOptions())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Name of the application'),
            'baseUrl' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Base URL'),
            'defaultLanguage' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Default language'),
            'timeZone' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Server Timezone'),
            'defaultSpaceGuid' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Default space'),
            'tour' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Show introduction tour for new users'),
            'dashboardShowProfilePostForm' => Yii::t('AdminModule.forms_BasicSettingsForm',
                'Show user profile post form on dashboard'),
            'enableFriendshipModule' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Enable user friendship system'),
            'defaultStreamSort' => Yii::t('AdminModule.forms_BasicSettingsForm', 'Default stream content order'),
        ];
    }

    /**
     * This validator function checks the defaultSpaceGuid.
     * @param type $attribute
     * @param type $params
     */
    public function checkSpaceGuid($attribute, $params)
    {
        if (!empty($this->defaultSpaceGuid)) {
            foreach ($this->defaultSpaceGuid as $spaceGuid) {
                if ($spaceGuid != "") {
                    $space = \humhub\modules\space\models\Space::findOne(['guid' => $spaceGuid]);
                    if ($space == null) {
                        $this->addError($attribute, Yii::t('AdminModule.forms_BasicSettingsForm', "Invalid space"));
                    }
                }
            }
        }
    }

    /**
     * Saves the form
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
        Yii::$app->getModule('friendship')->settings->set('enable', $this->enableFriendshipModule);
        Yii::$app->getModule('stream')->settings->set('defaultSort', $this->defaultStreamSort);

        // Remove Old Default Spaces
        if (empty($this->defaultSpaceGuid)) {
            Space::updateAll(['auto_add_new_members' => 0], ['auto_add_new_members' => 1]);
        } else {
            foreach (Space::findAll(['auto_add_new_members' => 1]) as $space) {
                if (!in_array($space->guid, $this->defaultSpaceGuid)) {
                    $space->auto_add_new_members = 0;
                    $space->save();
                }
            }
            // Add new Default Spaces
            foreach ($this->defaultSpaceGuid as $spaceGuid) {
                $space = Space::findOne(['guid' => $spaceGuid]);
                if ($space != null && $space->auto_add_new_members != 1) {
                    $space->auto_add_new_members = 1;
                    $space->save();
                }
            }
        }

        DynamicConfig::rewrite();

        return true;
    }

    /**
     * Returns available options for defaultStreamSort attribute
     * @return array
     */
    public function getDefaultStreamSortOptions()
    {
        return [
            Stream::SORT_CREATED_AT => Yii::t('AdminModule.forms_BasicSettingsForm', 'Sort by creation date'),
            Stream::SORT_UPDATED_AT => Yii::t('AdminModule.forms_BasicSettingsForm', 'Sort by update date'),
        ];
    }

}
