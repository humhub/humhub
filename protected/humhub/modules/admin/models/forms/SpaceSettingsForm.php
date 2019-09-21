<?php

namespace humhub\modules\admin\models\forms;

use humhub\modules\space\models\Space;
use Yii;

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.10
 */
class SpaceSettingsForm extends \yii\base\Model
{

    public $defaultVisibility;
    public $defaultJoinPolicy;
    public $defaultContentVisibility;
    public $defaultSpaceGuid = [];
    public $defaultSpaces;

    private $settings;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->defaultJoinPolicy = $this->getSettings()->get('defaultJoinPolicy');
        $this->defaultVisibility = $this->getSettings()->get('defaultVisibility');
        $this->defaultContentVisibility = $this->getSettings()->get('defaultContentVisibility');
        $this->defaultSpaces = \humhub\modules\space\models\Space::findAll(['auto_add_new_members' => 1]);
    }

    private function getSettings()
    {
        if(!$this->settings) {
            $this->settings = Yii::$app->getModule('space')->settings;
        }
        return $this->settings;
    }

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return [
            [['defaultVisibility', 'defaultJoinPolicy', 'defaultContentVisibility'], 'integer'],
            ['defaultSpaceGuid', 'checkSpaceGuid'],
        ];
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return [
            'defaultSpaceGuid' => Yii::t('AdminModule.space', 'Default space'),
            'defaultVisibility' => Yii::t('AdminModule.space', 'Default Visibility'),
            'defaultJoinPolicy' => Yii::t('AdminModule.space', 'Default Join Policy'),
            'defaultContentVisibility' => Yii::t('AdminModule.space', 'Default Content Visiblity'),
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
                        $this->addError($attribute, Yii::t('AdminModule.space', "Invalid space"));
                    }
                }
            }
        }
    }

    public function save()
    {
        $this->getSettings()->set('defaultJoinPolicy', $this->defaultJoinPolicy);
        $this->getSettings()->set('defaultVisibility', $this->defaultVisibility);
        $this->getSettings()->set('defaultContentVisibility', $this->defaultContentVisibility);
        $this->updateDefaultSpaces();
        return true;
    }

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
