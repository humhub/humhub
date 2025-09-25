<?php

namespace humhub\modules\admin\models\forms;

use humhub\libs\DynamicConfig;
use humhub\modules\ui\icon\widgets\Icon;
use Yii;
use yii\base\Model;
use yii\bootstrap\Alert;

/**
 * BasicSettingsForm
 * @since 0.5
 */
class BasicSettingsForm extends Model
{
    public $name;
    public $baseUrl;
    public $defaultLanguage;
    public $tour;
    public $defaultTimeZone;
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
            [['defaultTimeZone'], 'in', 'range' => \DateTimeZone::listIdentifiers()],
            [['tour', 'dashboardShowProfilePostForm', 'enableFriendshipModule', 'maintenanceMode'], 'in', 'range' => [0, 1]],
            [['baseUrl'], 'url', 'pattern' => '/^{schemes}:\/\/([A-Z0-9][A-Z0-9_\-\.]*)+(?::\d{1,5})?(?:$|[?\/#])/i'],
            [['baseUrl'], 'trim'],
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
            'tour' => Yii::t('AdminModule.settings', 'Show introduction tour for new users'),
            'dashboardShowProfilePostForm' => Yii::t('AdminModule.settings', 'Show user profile post form on dashboard'),
            'enableFriendshipModule' => Yii::t('AdminModule.settings', 'Enable user friendship system'),
            'defaultStreamSort' => Yii::t('AdminModule.settings', 'Default stream content order'),
            'maintenanceMode' => Yii::t('AdminModule.settings', 'Enable maintenance mode'),
            'maintenanceModeInfo' => Yii::t('AdminModule.settings', 'Add custom info text for maintenance mode. Displayed on the login page.'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'baseUrl' => Yii::t('AdminModule.settings', 'E.g. http://example.com/humhub'),
            'maintenanceMode' => Alert::widget([
                'options' => ['class' => 'alert-danger'],
                'body'
                    => Icon::get('exclamation-triangle') . ' '
                    . Yii::t('AdminModule.settings', 'Maintenance mode restricts access to the platform and immediately logs out all users except Admins.'),
            ]),
        ];
    }

    /**
     * Saves the form
     * @return bool
     */
    public function save()
    {
        Yii::$app->settings->set('name', $this->name);
        Yii::$app->settings->set('baseUrl', $this->baseUrl);
        Yii::$app->settings->set('defaultLanguage', $this->defaultLanguage);
        Yii::$app->settings->set('defaultTimeZone', $this->defaultTimeZone);
        Yii::$app->settings->set('maintenanceMode', $this->maintenanceMode);
        Yii::$app->settings->set('maintenanceModeInfo', $this->maintenanceModeInfo);

        Yii::$app->getModule('dashboard')->settings->set('showProfilePostForm', $this->dashboardShowProfilePostForm);
        Yii::$app->getModule('tour')->settings->set('enable', $this->tour);
        Yii::$app->getModule('friendship')->settings->set('enable', $this->enableFriendshipModule);

        DynamicConfig::rewrite();

        return true;
    }
}
