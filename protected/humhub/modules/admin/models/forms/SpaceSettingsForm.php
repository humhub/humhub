<?php

namespace humhub\modules\admin\models\forms;

use humhub\components\SettingsManager;
use humhub\modules\space\models\Space;
use humhub\modules\space\Module;
use Yii;

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.10
 */
class SpaceSettingsForm extends \yii\base\Model
{
    /**
     * @var int
     */
    public $defaultVisibility;

    /**
     * @var int
     */
    public $defaultJoinPolicy;

    /**
     * @var int
     */
    public $defaultContentVisibility;

    /**
     * @var string[]
     */
    public $defaultSpaceGuid = [];

    /**
     * @var Space[]
     */
    public $defaultSpaces;

    /**
     * @var string|null
     */
    public $defaultIndexRoute = null;

    /**
     * @var string|null
     */
    public $defaultIndexGuestRoute = null;

    /**
     * @var bool
     */
    public $defaultHideMembers = false;

    /**
     * @var bool
     */
    public $defaultHideActivities = false;

    /**
     * @var bool
     */
    public $defaultHideAbout = false;

    /**
     * @var bool
     */
    public $defaultHideFollowers = false;

    /**
     * @var SettingsManager|null
     */
    public ?SettingsManager $settingsManager;

    /**
     * @inerhitdoc
     */
    public function rules()
    {
        return [
            [['defaultVisibility', 'defaultJoinPolicy', 'defaultContentVisibility'], 'integer'],
            ['defaultSpaceGuid', 'checkSpaceGuid'],
            [['defaultIndexRoute', 'defaultIndexGuestRoute'], 'string'],
            [['defaultHideMembers', 'defaultHideActivities', 'defaultHideAbout', 'defaultHideFollowers'], 'boolean'],
        ];
    }

    /**
     * @inerhitdoc
     */
    public function attributeLabels()
    {
        return [
            'defaultSpaceGuid' => Yii::t('AdminModule.space', 'Default space'),
            'defaultVisibility' => Yii::t('AdminModule.space', 'Default Visibility'),
            'defaultJoinPolicy' => Yii::t('AdminModule.space', 'Default Join Policy'),
            'defaultContentVisibility' => Yii::t('AdminModule.space', 'Default Content Visiblity'),
            'defaultIndexRoute' => Yii::t('AdminModule.space', 'Default Homepage'),
            'defaultIndexGuestRoute' => Yii::t('AdminModule.space', 'Default Homepage (Non-members)'),
            'defaultHideMembers' => Yii::t('AdminModule.space', 'Default "Hide Members"'),
            'defaultHideActivities' => Yii::t('AdminModule.space', 'Default "Hide Activity Sidebar Widget"'),
            'defaultHideAbout' => Yii::t('AdminModule.space', 'Default "Hide About Page"'),
            'defaultHideFollowers' => Yii::t('AdminModule.space', 'Default "Hide Followers"'),
        ];
    }

    /**
     * This validator function checks the defaultSpaceGuid.
     * @param $attribute
     * @param $params
     */
    public function checkSpaceGuid($attribute, $params)
    {
        if (!empty($this->defaultSpaceGuid)) {
            foreach ($this->defaultSpaceGuid as $spaceGuid) {
                if ($spaceGuid != "") {
                    $space = Space::findOne(['guid' => $spaceGuid]);
                    if ($space == null) {
                        $this->addError($attribute, Yii::t('AdminModule.space', "Invalid space"));
                    }
                }
            }
        }
    }

    /**
     * @return void
     */
    public function loadBySettings(): void
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('space');

        $this->defaultJoinPolicy = (int)$this->settingsManager->get('defaultJoinPolicy');
        $this->defaultVisibility = (int)$this->settingsManager->get('defaultVisibility');
        $this->defaultContentVisibility = (int)$this->settingsManager->get('defaultContentVisibility');
        $this->defaultSpaces = Space::findAll(['auto_add_new_members' => 1]);
        $this->defaultIndexRoute = $this->settingsManager->get('defaultIndexRoute');
        $this->defaultIndexGuestRoute = $this->settingsManager->get('defaultIndexGuestRoute');
        $this->defaultHideMembers = $this->settingsManager->get('defaultHideMembers', $module->hideMembers);
        $this->defaultHideActivities = $this->settingsManager->get('defaultHideActivities', $module->hideActivities);
        $this->defaultHideAbout = $this->settingsManager->get('defaultHideAbout', $module->hideAboutPage);
        $this->defaultHideFollowers = $this->settingsManager->get('defaultHideFollowers', $module->hideFollowers);
    }

    /**
     * @return bool
     */
    public function save(): bool
    {
        if (!$this->validate()) {
            return false;
        }

        $this->settingsManager->set('defaultJoinPolicy', $this->defaultJoinPolicy);
        $this->settingsManager->set('defaultVisibility', $this->defaultVisibility);
        $this->settingsManager->set('defaultContentVisibility', $this->defaultContentVisibility);
        $this->settingsManager->set('defaultIndexRoute', $this->defaultIndexRoute);
        $this->settingsManager->set('defaultIndexGuestRoute', $this->defaultIndexGuestRoute);
        $this->settingsManager->set('defaultHideMembers', $this->defaultHideMembers);
        $this->settingsManager->set('defaultHideActivities', $this->defaultHideActivities);
        $this->settingsManager->set('defaultHideAbout', $this->defaultHideAbout);
        $this->settingsManager->set('defaultHideFollowers', $this->defaultHideFollowers);
        $this->updateDefaultSpaces();

        return true;
    }

    /**
     * @return void
     */
    private function updateDefaultSpaces()
    {
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
    }

}
