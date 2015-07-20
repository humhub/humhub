<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
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
    public $visibility;
    public $timeZone;

    /**
     * Declares the validation rules.
     */
    public function rules()
    {
        return array(
            ['tags', 'string', 'max' => 100],
            [['show_introduction_tour'], 'boolean'],
            [['timeZone'], 'in', 'range' => \DateTimeZone::listIdentifiers()],
            ['language', 'in', 'range' => array_keys(Yii::$app->params['availableLanguages'])],
            ['visibility', 'in', 'range' => [1, 2]],
        );
    }

    /**
     * Declares customized attribute labels.
     * If not declared here, an attribute would have a label that is
     * the same as its name with the first letter in upper case.
     */
    public function attributeLabels()
    {
        return array(
            'tags' => Yii::t('UserModule.forms_AccountSettingsForm', 'Tags'),
            'language' => Yii::t('UserModule.forms_AccountSettingsForm', 'Language'),
            'show_introduction_tour' => Yii::t('UserModule.forms_AccountSettingsForm', 'Hide panel on dashboard'),
            'timeZone' => Yii::t('UserModule.forms_AccountSettingsForm', 'TimeZone'),
            'visibility' => Yii::t('UserModule.forms_AccountSettingsForm', 'Profile visibility'),
        );
    }

}
