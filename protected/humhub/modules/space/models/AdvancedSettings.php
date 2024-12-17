<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\models;

use humhub\modules\admin\models\forms\SpaceSettingsForm;
use humhub\modules\admin\permissions\ManageSpaces;
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
     * @var string|null
     */
    public $defaultStreamSort = null;

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
     * @var int
     */
    public $sortOrder;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['sortOrder'], 'required'],
            [['sortOrder'], 'integer'],
            [['indexUrl', 'indexGuestUrl', 'defaultStreamSort'], 'string'],
            ['defaultStreamSort', 'in', 'range' => array_keys(SpaceSettingsForm::defaultStreamSortOptions())],
            [['hideMembers', 'hideActivities', 'hideAbout', 'hideFollowers'], 'boolean'],
            ['url', UrlValidator::class, 'space' => $this->space],
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
            'indexGuestUrl' => Yii::t('SpaceModule.base', 'Homepage (Non-members)'),
            'defaultStreamSort' => Yii::t('SpaceModule.base', 'Stream Sort'),
            'hideMembers' => Yii::t('SpaceModule.base', 'Hide Members'),
            'hideActivities' => Yii::t('SpaceModule.base', 'Hide Activity Sidebar Widget'),
            'hideAbout' => Yii::t('SpaceModule.base', 'Hide About Page'),
            'hideFollowers' => Yii::t('SpaceModule.base', 'Hide Followers'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeHints()
    {
        return [
            'indexUrl' => Yii::t('SpaceModule.base', 'The default homepage for members of this Space'),
            'indexGuestUrl' => Yii::t('SpaceModule.base', 'The default homepage for non-members and guests visiting this Space'),
            'defaultStreamSort' => Yii::t('SpaceModule.base', 'Default Stream Sort'),
        ];
    }

    public function loadBySettings()
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('space');

        $settings = $this->space->getSettings();
        $defaultSettings = $module->getDefaultSettings();

        $this->url = $this->space->url;
        $this->indexUrl = $settings->get('indexUrl', $defaultSettings->defaultIndexRoute ? $this->space->createUrl($defaultSettings->defaultIndexRoute) : '');
        $this->indexGuestUrl = $settings->get('indexGuestUrl', $defaultSettings->defaultIndexGuestRoute ? $this->space->createUrl($defaultSettings->defaultIndexGuestRoute) : '');
        $this->defaultStreamSort = $settings->get('defaultStreamSort', $defaultSettings->defaultStreamSort);

        $this->hideMembers = $settings->get('hideMembers', $defaultSettings->defaultHideMembers);
        $this->hideAbout = $settings->get('hideAbout', $defaultSettings->defaultHideAbout);
        $this->hideActivities = $settings->get('hideActivities', $defaultSettings->defaultHideActivities);
        $this->hideFollowers = $settings->get('hideFollowers', $defaultSettings->defaultHideFollowers);
        $this->sortOrder = $this->space->sort_order;
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

        if ($this->space->isAttributeChanged('url')) {
            if ($this->indexUrl && $oldUrl = $this->space->getOldAttribute('url')) {
                $this->indexUrl = str_replace(
                    $oldUrl,
                    $this->space->url,
                    $this->indexUrl,
                );
            }
            if ($this->indexGuestUrl && $oldUrl = $this->space->getOldAttribute('url')) {
                $this->indexGuestUrl = str_replace(
                    $oldUrl,
                    $this->space->url,
                    $this->indexGuestUrl,
                );
            }
        }

        if (Yii::$app->user->can(ManageSpaces::class)) {
            $this->space->sort_order = $this->sortOrder;
        }

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

        $settings->set('defaultStreamSort', $this->defaultStreamSort);
        $settings->set('hideMembers', (bool)$this->hideMembers);
        $settings->set('hideAbout', (bool)$this->hideAbout);
        $settings->set('hideActivities', (bool)$this->hideActivities);
        $settings->set('hideFollowers', (bool)$this->hideFollowers);

        return true;
    }
}
