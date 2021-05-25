<?php

namespace humhub\modules\admin\models\forms;

use Yii;
use humhub\libs\DynamicConfig;
use humhub\modules\space\models\Space;
use humhub\modules\stream\actions\Stream;
use humhub\libs\TimezoneHelper;

/**
 * BasicSettingsForm
 * @since 0.5
 */
class BasicSettingsForm extends \yii\base\Model
{

    public $name;
    public $baseUrl;
    public $defaultLanguage;
    public $tour;
    public $defaultTimeZone;
    public $timeZone;
    public $dashboardShowProfilePostForm;
    public $enableFriendshipModule;
    public $maintenanceMode;
    public $maintenanceModeInfo;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->name = Yii::$app->settings->get('name');
        $this->baseUrl = Yii::$app->settings->get('baseUrl');
        $this->defaultLanguage = Yii::$app->settings->get('defaultLanguage');
        $this->defaultTimeZone = Yii::$app->settings->get('defaultTimeZone');
        $this->timeZone = Yii::$app->settings->get('timeZone');
        $this->maintenanceMode = Yii::$app->settings->get('maintenanceMode');
        $this->maintenanceModeInfo = Yii::$app->settings->get('maintenanceModeInfo');

        $this->dashboardShowProfilePostForm = Yii::$app->getModule('dashboard')->settings->get('showProfilePostForm');
        $this->tour = Yii::$app->getModule('tour')->settings->get('enable');
        $this->enableFriendshipModule = Yii::$app->getModule('friendship')->settings->get('enable');
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
            [['defaultTimeZone', 'timeZone'], 'in', 'range' => \DateTimeZone::listIdentifiers()],
            [['tour', 'dashboardShowProfilePostForm', 'enableFriendshipModule', 'maintenanceMode'], 'in', 'range' => [0, 1]],
            [['baseUrl'], function ($attribute, $params, $validator) {
                if (substr($this->$attribute, 0, 7) !== 'http://' && substr($this->$attribute, 0, 8) !== 'https://') {
                    $this->addError($attribute, Yii::t('AdminModule.base', 'Base URL needs to begin with http:// or https://'));
                }
            }],
            ['maintenanceModeInfo', 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => Yii::t('AdminModule.settings', 'Name of the application'),
            'baseUrl' => Yii::t('AdminModule.settings', 'Base URL'),
            'defaultLanguage' => Yii::t('AdminModule.settings', 'Default language'),
            'defaultTimeZone' => Yii::t('AdminModule.settings', 'Default Timezone'),
            'timeZone' => Yii::t('AdminModule.settings', 'Server Timezone'),
            'tour' => Yii::t('AdminModule.settings', 'Show introduction tour for new users'),
            'dashboardShowProfilePostForm' => Yii::t('AdminModule.settings', 'Show user profile post form on dashboard'),
            'enableFriendshipModule' => Yii::t('AdminModule.settings', 'Enable user friendship system'),
            'defaultStreamSort' => Yii::t('AdminModule.settings', 'Default stream content order'),
            'maintenanceMode' => Yii::t('AdminModule.settings', 'Enable maintenance mode'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'defaultTimeZone' => Yii::t('AdminModule.settings', 'Reported database time: {dateTime}', [
                    'dateTime' => Yii::$app->formatter->asTime(TimezoneHelper::getDatabaseConnectionTime())
                ]
            ),
            'timeZone' => Yii::t('AdminModule.settings', 'Reported database time: {dateTime}', [
                    'dateTime' => Yii::$app->formatter->asTime(TimezoneHelper::getDatabaseConnectionTime())
                ]
            ),
            'baseUrl' => Yii::t('AdminModule.settings', 'E.g. http://example.com/humhub'),
            'maintenanceModeInfo' => Yii::t('AdminModule.settings', 'Add custom info text for maintenance mode. Displayed on the login page.'),
        ];
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
        Yii::$app->settings->set('defaultTimeZone', $this->defaultTimeZone);
        Yii::$app->settings->set('timeZone', $this->timeZone);
        Yii::$app->settings->set('maintenanceMode', $this->maintenanceMode);
        Yii::$app->settings->set('maintenanceModeInfo', $this->maintenanceModeInfo);

        Yii::$app->getModule('dashboard')->settings->set('showProfilePostForm', $this->dashboardShowProfilePostForm);
        Yii::$app->getModule('tour')->settings->set('enable', $this->tour);
        Yii::$app->getModule('friendship')->settings->set('enable', $this->enableFriendshipModule);

        DynamicConfig::rewrite();

        return true;
    }

}
