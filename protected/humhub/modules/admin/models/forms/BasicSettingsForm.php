<?php

namespace humhub\modules\admin\models\forms;

use humhub\modules\content\actions\Stream;
use humhub\modules\post\Module as PostModule;
use humhub\modules\user\components\MaintenanceModeGate;
use humhub\modules\user\models\ProfileField;
use Yii;
use yii\base\Model;

/**
 * BasicSettingsForm
 * @since 0.5
 */
class BasicSettingsForm extends Model
{
    public $baseUrl;
    public $tour;
    public $dashboardShowProfilePostForm;
    public $dashboardShowTopicSidebar;
    public $enableFriendshipModule;
    public $maintenanceMode;
    public $maintenanceModeInfo;
    public $paginationSize;
    public $displayNameFormat;
    public $displayNameSubFormat;
    public $spaceOrder;
    public $defaultStreamSort;
    public $postTitleMode;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->baseUrl = Yii::$app->settings->get('baseUrl');
        $this->maintenanceMode = Yii::$app->settings->get(MaintenanceModeGate::SETTING_MAINTENANCE_MODE);
        $this->maintenanceModeInfo = Yii::$app->settings->get('maintenanceModeInfo');

        $this->dashboardShowProfilePostForm = Yii::$app->getModule('dashboard')->settings->get('showProfilePostForm');
        $this->dashboardShowTopicSidebar = Yii::$app->getModule('dashboard')->settings->get('showTopicSidebar');
        $this->tour = Yii::$app->getModule('tour')->settings->get('enable');
        $this->enableFriendshipModule = Yii::$app->getModule('friendship')->settings->get('enable');

        $this->paginationSize = Yii::$app->settings->get('paginationSize');
        $this->displayNameFormat = Yii::$app->settings->get('displayNameFormat');
        $this->displayNameSubFormat = Yii::$app->settings->get('displayNameSubFormat');
        $this->spaceOrder = Yii::$app->getModule('space')->settings->get('spaceOrder');
        $this->defaultStreamSort = Yii::$app->getModule('content')->settings->get('defaultStreamSort');
        $this->postTitleMode = Yii::$app->getModule('post')->settings->get('titleMode', PostModule::TITLE_MODE_OFF);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['baseUrl', 'paginationSize'], 'required'],
            [['tour', 'dashboardShowProfilePostForm', 'dashboardShowTopicSidebar', 'enableFriendshipModule', 'maintenanceMode'], 'in', 'range' => [0, 1]],
            [['baseUrl'], 'url', 'pattern' => '/^{schemes}:\/\/([A-Z0-9][A-Z0-9_\-\.]*)+(?::\d{1,5})?(?:$|[?\/#])/i'],
            [['baseUrl'], 'trim'],
            ['maintenanceModeInfo', 'safe'],
            ['paginationSize', 'integer', 'max' => 200, 'min' => 1],
            [['displayNameFormat', 'displayNameSubFormat', 'spaceOrder'], 'safe'],
            ['defaultStreamSort', 'in', 'range' => array_keys($this->getDefaultStreamSortOptions())],
            ['postTitleMode', 'in', 'range' => array_keys($this->getPostTitleModeOptions())],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'baseUrl' => Yii::t('AdminModule.settings', 'Base URL'),
            'tour' => Yii::t('AdminModule.settings', 'Show introduction tour for new users'),
            'dashboardShowProfilePostForm' => Yii::t('AdminModule.settings', 'Show user profile post form on dashboard'),
            'dashboardShowTopicSidebar' => Yii::t('AdminModule.settings', 'Show topic widget in Dashboard sidebar'),
            'enableFriendshipModule' => Yii::t('AdminModule.settings', 'Enable user friendship system'),
            'maintenanceMode' => Yii::t('AdminModule.settings', 'Enable maintenance mode'),
            'maintenanceModeInfo' => Yii::t('AdminModule.settings', 'Add custom info text for maintenance mode. Displayed on the login page.'),
            'paginationSize' => Yii::t('AdminModule.settings', 'Default pagination size (Entries per page)'),
            'displayNameFormat' => Yii::t('AdminModule.settings', 'User Display Name'),
            'displayNameSubFormat' => Yii::t('AdminModule.settings', 'User Display Name Subtitle'),
            'spaceOrder' => Yii::t('AdminModule.settings', '"My Spaces" Sorting'),
            'defaultStreamSort' => Yii::t('AdminModule.settings', 'Default stream content order'),
            'postTitleMode' => Yii::t('AdminModule.settings', 'Post title'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'baseUrl' => Yii::t('AdminModule.settings', 'E.g. http://example.com/humhub'),
            'spaceOrder' => Yii::t('AdminModule.settings', 'Custom sort order can be defined in the Space advanced settings.'),
            'postTitleMode' => Yii::t('AdminModule.settings', 'Allows posts to have an additional title shown above the content.'),
        ];
    }

    /**
     * Saves the form
     * @return bool
     */
    public function save()
    {
        Yii::$app->settings->set('baseUrl', $this->baseUrl);
        Yii::$app->settings->set(MaintenanceModeGate::SETTING_MAINTENANCE_MODE, $this->maintenanceMode);
        Yii::$app->settings->set('maintenanceModeInfo', $this->maintenanceModeInfo);

        Yii::$app->getModule('dashboard')->settings->set('showProfilePostForm', $this->dashboardShowProfilePostForm);
        Yii::$app->getModule('dashboard')->settings->set('showTopicSidebar', $this->dashboardShowTopicSidebar);
        Yii::$app->getModule('tour')->settings->set('enable', $this->tour);
        Yii::$app->getModule('friendship')->settings->set('enable', $this->enableFriendshipModule);

        Yii::$app->settings->set('paginationSize', $this->paginationSize);
        Yii::$app->settings->set('displayNameFormat', $this->displayNameFormat);
        Yii::$app->settings->set('displayNameSubFormat', $this->displayNameSubFormat);
        Yii::$app->getModule('space')->settings->set('spaceOrder', $this->spaceOrder);
        Yii::$app->getModule('content')->settings->set('defaultStreamSort', $this->defaultStreamSort);
        Yii::$app->getModule('post')->settings->set('titleMode', $this->postTitleMode);

        return true;
    }

    /**
     * @return array the options of the defaultStreamSort attribute
     */
    public function getDefaultStreamSortOptions(): array
    {
        return [
            Stream::SORT_CREATED_AT => Yii::t('AdminModule.settings', 'Sort by creation date'),
            Stream::SORT_UPDATED_AT => Yii::t('AdminModule.settings', 'Sort by update date'),
        ];
    }

    /**
     * @return array the options of the postTitleMode attribute
     */
    public function getPostTitleModeOptions(): array
    {
        return [
            PostModule::TITLE_MODE_OFF => Yii::t('AdminModule.settings', 'Off'),
            PostModule::TITLE_MODE_OPTIONAL => Yii::t('AdminModule.settings', 'Optional'),
            PostModule::TITLE_MODE_REQUIRED => Yii::t('AdminModule.settings', 'Required'),
        ];
    }

    /**
     * @return array the profile fields that can be the subtitle of the display name
     */
    public function getDisplayNameSubAttributes(): array
    {
        $availableAttributes = [];
        foreach (ProfileField::find()->all() as $profileField) {
            $availableAttributes[$profileField->internal_name] = $profileField->title;
        }
        return $availableAttributes;
    }
}
