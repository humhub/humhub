<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\forms;

use Yii;

/**
 * Form Model for changing basic account settings
 *
 * @package humhub.modules_core.user.forms
 * @since 0.9
 */
class AccountSettings extends \yii\base\Model
{

    public $tags;
    public $language;
    public $show_introduction_tour;
    public $show_share_panel;
    public $visibility;
    public $timeZone;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array(
            ['tags', 'string', 'max' => 100],
            [['show_introduction_tour', 'show_share_panel'], 'boolean'],
            [['timeZone'], 'in', 'range' => \DateTimeZone::listIdentifiers()],
            ['language', 'in', 'range' => array_keys(Yii::$app->params['availableLanguages'])],
            ['visibility', 'in', 'range' => [1, 2]],
        );
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array(
            'tags' => Yii::t('UserModule.forms_AccountSettingsForm', 'Tags'),
            'language' => Yii::t('UserModule.forms_AccountSettingsForm', 'Language'),
            'show_introduction_tour' => Yii::t('UserModule.forms_AccountSettingsForm', 'Hide introduction tour panel on dashboard'),
            'show_share_panel' => Yii::t('UserModule.forms_AccountSettingsForm', 'Hide share panel on dashboard'),
            'timeZone' => Yii::t('UserModule.forms_AccountSettingsForm', 'TimeZone'),
            'visibility' => Yii::t('UserModule.forms_AccountSettingsForm', 'Profile visibility'),
        );
    }

}
