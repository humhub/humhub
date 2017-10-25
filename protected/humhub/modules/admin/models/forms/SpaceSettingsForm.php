<?php

namespace humhub\modules\admin\models\forms;

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

    private $settings;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->defaultJoinPolicy = $this->getSettings()->get('defaultJoinPolicy');
        $this->defaultVisibility = $this->getSettings()->get('defaultVisibility');
        $this->defaultContentVisibility = $this->getSettings()->get('defaultContentVisibility');
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
            'defaultVisibility' => Yii::t('AdminModule.forms_SpaceSettingsForm', 'Default Visibility'),
            'defaultJoinPolicy' => Yii::t('AdminModule.forms_SpaceSettingsForm', 'Default Join Policy'),
            'defaultContentVisibility' => Yii::t('AdminModule.forms_SpaceSettingsForm', 'Default Content Visiblity'),
        ];
    }

    public function save()
    {
        $this->getSettings()->set('defaultJoinPolicy', $this->defaultJoinPolicy);
        $this->getSettings()->set('defaultVisibility', $this->defaultVisibility);
        $this->getSettings()->set('defaultContentVisibility', $this->defaultContentVisibility);
        return true;
    }

}
