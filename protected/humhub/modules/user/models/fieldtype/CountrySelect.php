<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\modules\user\models\User;
use Yii;
use humhub\libs\Iso3166Codes;

/**
 * ProfileFieldTypeSelect handles numeric profile fields.
 *
 * @package humhub.modules_core.user.models
 * @since 0.5
 */
class CountrySelect extends Select
{

    /**
     * Returns Form Definition for edit/create this field.
     *
     * @return array Form Definition
     */
    public function getFormDefinition($definition = [])
    {
        return parent::getFormDefinition([
                    get_class($this) => [
                        'type' => 'form',
                        'title' => Yii::t('UserModule.models_ProfileFieldTypeSelect', 'Supported ISO3166 country codes'),
                        'elements' => [
                            'options' => [
                                'type' => 'textarea',
                                'label' => Yii::t('UserModule.models_ProfileFieldTypeSelect', 'Possible values'),
                                'class' => 'form-control',
                                'hint' => Yii::t('UserModule.models_ProfileFieldTypeSelect', 'Comma separated country codes, e.g. DE,EN,AU')
                            ]
                        ]
                    ]
        ]);
    }

    /**
     * Returns a list of possible options
     *
     * @return array
     */
    public function getSelectItems()
    {
        $items = [];

        // if no options set basically return a translated map of all defined countries
        if (empty($this->options) || trim($this->options) == false) {
            $items = iso3166Codes::$countries;
            foreach ($items as $code => $value) {
                $items[$code] = iso3166Codes::country($code);
            }
        } else {
            foreach (explode(",", $this->options) as $code) {

                $key = trim($code);
                $value = iso3166Codes::country($key, true);
                if (!empty($key) && $key !== $value) {
                    $items[trim($key)] = trim($value);
                }
            }
        }

        // Sort countries list based on user language   
        $col = new \Collator(Yii::$app->language);
        $col->asort($items);

        return $items;
    }

    /**
     * Returns value of option
     *
     * @param User $user            
     * @param Boolean $raw
     *            Output Key
     * @return String
     */
    public function getUserValue($user, $raw = true)
    {
        $internalName = $this->profileField->internal_name;
        $value = $user->profile->$internalName;

        if (!$raw) {
            return \yii\helpers\Html::encode(iso3166Codes::country($value));
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getFieldFormDefinition()
    {
        $definition = parent::getFieldFormDefinition();
        $definition[$this->profileField->internal_name]['htmlOptions'] = ['data-ui-select2' => true, 'style' => 'width:100%'];
        return $definition;
    }

}
