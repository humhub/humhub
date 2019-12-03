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
            [['tour', 'dashboardShowProfilePostForm', 'enableFriendshipModule'], 'in', 'range' => [0, 1]],
            [['defaultStreamSort'], 'in', 'range' => array_keys($this->getDefaultStreamSortOptions())],
            [['baseUrl'], function ($attribute, $params, $validator) {
                    if (substr($this->$attribute, 0, 7) !== 'http://' && substr($this->$attribute, 0, 8) !== 'https://') {
                        $this->addError($attribute, Yii::t('AdminModule.base', 'Base URL needs to begin with http:// or https://'));
                    }
                }],
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
            'timeZone' => Yii::t('AdminModule.settings', 'Server Timezone'),
            'tour' => Yii::t('AdminModule.settings', 'Show introduction tour for new users'),
            'dashboardShowProfilePostForm' => Yii::t('AdminModule.settings', 'Show user profile post form on dashboard'),
            'enableFriendshipModule' => Yii::t('AdminModule.settings', 'Enable user friendship system'),
            'defaultStreamSort' => Yii::t('AdminModule.settings', 'Default stream content order'),
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
        Yii::$app->settings->set('timeZone', $this->timeZone);

        Yii::$app->getModule('dashboard')->settings->set('showProfilePostForm', $this->dashboardShowProfilePostForm);
        Yii::$app->getModule('tour')->settings->set('enable', $this->tour);
        Yii::$app->getModule('friendship')->settings->set('enable', $this->enableFriendshipModule);
        Yii::$app->getModule('stream')->settings->set('defaultSort', $this->defaultStreamSort);

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
            Stream::SORT_CREATED_AT => Yii::t('AdminModule.settings', 'Sort by creation date'),
            Stream::SORT_UPDATED_AT => Yii::t('AdminModule.settings', 'Sort by update date'),
        ];
    }

}
