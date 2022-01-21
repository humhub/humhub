<?php

namespace humhub\modules\admin\models\forms;

use Yii;

/**
 * @package humhub.modules_core.admin.forms
 * @since 0.10
 */
class OEmbedProviderForm extends \yii\base\Model
{

    public $name;
    public $endpoint;
    public $pattern;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return [
            [['name', 'pattern', 'endpoint'], 'string'],
            [['name', 'pattern', 'endpoint'], 'required'],
            ['endpoint', 'url'],
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
            'name' => Yii::t('AdminModule.settings', 'Provider Name'),
            'endpoint' => Yii::t('AdminModule.settings', 'Endpoint Url'),
            'pattern' => Yii::t('AdminModule.settings', 'Url Pattern'),
        ];
    }

}
