<?php

namespace humhub\modules\admin\models\forms;

use humhub\components\SettingsManager;
use humhub\modules\space\models\Space;
use humhub\modules\space\Module;
use humhub\modules\stream\actions\Stream;
use humhub\modules\stream\widgets\WallStreamFilterNavigation;
use humhub\modules\topic\jobs\ConvertTopicsToGlobalJob;
use Yii;
use yii\base\Model;

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.10
 */
class SpaceSettingsForm extends Model
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
     * @var string|null
     */
    public $defaultStreamSort = null;

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
     * @var bool
     */
    public $allowSpaceTopics = true;

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
            [['defaultIndexRoute', 'defaultIndexGuestRoute', 'defaultStreamSort'], 'string'],
            ['defaultStreamSort', 'in', 'range' => array_keys(self::defaultStreamSortOptions())],
            [['defaultHideMembers', 'defaultHideActivities', 'defaultHideAbout', 'defaultHideFollowers', 'allowSpaceTopics'], 'boolean'],
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
            'defaultStreamSort' => Yii::t('AdminModule.space', 'Default Stream Sort'),
            'defaultHideMembers' => Yii::t('AdminModule.space', 'Default "Hide Members"'),
            'defaultHideActivities' => Yii::t('AdminModule.space', 'Default "Hide Activity Sidebar Widget"'),
            'defaultHideAbout' => Yii::t('AdminModule.space', 'Default "Hide About Page"'),
            'defaultHideFollowers' => Yii::t('AdminModule.space', 'Default "Hide Followers"'),
            'allowSpaceTopics' => Yii::t('AdminModule.space', 'Allow individual topics in Spaces'),
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
        $this->defaultStreamSort = $this->settingsManager->get('defaultStreamSort', WallStreamFilterNavigation::FILTER_SORT_CREATION);
        $this->defaultHideMembers = $this->settingsManager->get('defaultHideMembers', $module->hideMembers);
        $this->defaultHideActivities = $this->settingsManager->get('defaultHideActivities', $module->hideActivities);
        $this->defaultHideAbout = $this->settingsManager->get('defaultHideAbout', $module->hideAboutPage);
        $this->defaultHideFollowers = $this->settingsManager->get('defaultHideFollowers', $module->hideFollowers);
        $this->allowSpaceTopics = $this->settingsManager->get('allowSpaceTopics', true);
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
        $this->settingsManager->set('defaultStreamSort', $this->defaultStreamSort);
        $this->settingsManager->set('defaultHideMembers', $this->defaultHideMembers);
        $this->settingsManager->set('defaultHideActivities', $this->defaultHideActivities);
        $this->settingsManager->set('defaultHideAbout', $this->defaultHideAbout);
        $this->settingsManager->set('defaultHideFollowers', $this->defaultHideFollowers);
        $this->settingsManager->set('allowSpaceTopics', $this->allowSpaceTopics);
        $this->updateDefaultSpaces();

        if (!$this->allowSpaceTopics) {
            Yii::$app->queue->push(new ConvertTopicsToGlobalJob([
                'containerType' => Space::class,
            ]));
        }

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

    public static function defaultStreamSortOptions(): array
    {
        return [
            Stream::SORT_CREATED_AT => Yii::t('ContentModule.base', 'Creation time'),
            Stream::SORT_UPDATED_AT => Yii::t('ContentModule.base', 'Last update'),
        ];
    }

}
