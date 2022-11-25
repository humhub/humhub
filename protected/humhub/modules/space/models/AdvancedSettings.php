<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\models;

use humhub\modules\space\components\UrlValidator;
use humhub\modules\space\Module;
use Yii;
use yii\base\Model;

/**
 * Model class for Advanced Settings of a Space. These settings are mainly stored via the
 * Settings Manager as the Space Model.
 *
 * @since 1.13
 * @author Luke
 */
class AdvancedSettings extends Model
{
    /**
     * @var Space
     */
    public $space;

    /**
     * @var string|null
     */
    public $url = null;

    /**
     * @var string|null
     */
    public $indexUrl = null;

    /**
     * @var string|null
     */
    public $indexGuestUrl = null;

    /**
     * @var bool
     */
    public $hideMembers = false;

    /**
     * @var bool
     */
    public $hideActivities = false;

    /**
     * @var bool
     */
    public $hideAbout = false;

    /**
     * @var bool
     */
    public $hideFollowers = false;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['indexUrl', 'indexGuestUrl'], 'string'],
            [['hideMembers', 'hideActivities', 'hideAbout', 'hideFollowers'], 'boolean'],
            ['url', UrlValidator::class, 'space' => $this->space]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'url' => 'URL',
            'indexUrl' => Yii::t('SpaceModule.base', 'Homepage'),
            'indexGuestUrl' => Yii::t('SpaceModule.base', 'Homepage (Guests)'),
            'hideMembers' => Yii::t('SpaceModule.base', 'Hide Members'),
            'hideActivities' => Yii::t('SpaceModule.base', 'Hide Activity Sidebar Widget'),
            'hideAbout' => Yii::t('SpaceModule.base', 'Hide About Page'),
            'hideFollowers' => Yii::t('SpaceModule.base', 'Hide Followers'),
        ];
    }


    public function loadBySettings()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('space');

        $settings = $this->space->getSettings();

        $this->url = $this->space->url;
        $this->indexUrl = $settings->get('indexUrl', null);
        $this->indexGuestUrl = $settings->get('indexGuestUrl', null);

        $this->hideMembers = $settings->get('hideMembers', $this->hideMembers);
        $this->hideAbout = $settings->get('hideAbout', $module->hideAboutPage);
        $this->hideActivities = $settings->get('hideActivities', $this->hideActivities);
        $this->hideFollowers = $settings->get('hideFollowers', $this->hideFollowers);
    }

    /**
     * @inheritdoc
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $settings = $this->space->getSettings();

        $this->space->url = $this->url;
        $this->space->save();

        if (!empty($this->indexUrl)) {
            $settings->set('indexUrl', $this->indexUrl);
        } else {
            $settings->delete('indexUrl');
        }

        if (!empty($this->indexGuestUrl)) {
            $settings->set('indexGuestUrl', $this->indexGuestUrl);
        } else {
            $settings->delete('indexGuestUrl');
        }

        $settings->set('hideMembers', (bool)$this->hideMembers);
        $settings->set('hideAbout', (bool)$this->hideAbout);
        $settings->set('hideActivities', (bool)$this->hideActivities);
        $settings->set('hideFollowers', (bool)$this->hideFollowers);

        return true;
    }

}
