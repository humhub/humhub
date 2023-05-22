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
    public $access_token;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return [
            [['name', 'pattern', 'endpoint'], 'string'],
            [['name', 'pattern', 'endpoint'], 'required'],
            ['endpoint', 'url'],
            ['access_token', 'required', 'when' => function($model) {
                parse_str($model->endpoint, $query);
                return isset($query['access_token']);
            }]
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
            'access_token' => Yii::t('AdminModule.settings', 'Access Token'),
        ];
    }

}
