<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\libs\Iso3166Codes;
use humhub\modules\user\models\User;
use humhub\libs\Html;
use Yii;

/**
 * ProfileFieldTypeSelect handles numeric profile fields.
 *
 * @package humhub\modules\user\models\fieldtype
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
                'title' => Yii::t('UserModule.profile', 'Supported ISO3166 country codes'),
                'elements' => [
                    'options' => [
                        'type' => 'textarea',
                        'label' => Yii::t('UserModule.profile', 'Possible values'),
                        'class' => 'form-control',
                        'hint' => Yii::t('UserModule.profile', 'Comma separated country codes, e.g. DE,EN,AU')
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
            $isoCodes = Iso3166Codes::$countries;

            foreach ($isoCodes as $code => $value) {
                $items[Iso3166Codes::country($code)] = Iso3166Codes::country($code);
            }
        } else {
            foreach (explode(",", $this->options) as $code) {
                $key = trim($code);
                $value = Iso3166Codes::country($key, true);
                if (!empty($key) && $key !== $value) {
                    $items[$key] = trim($value);
                }
            }
        }

        // Sort countries list based on user language
        $col = new \Collator(Yii::$app->language);
        $col->asort($items);

        return $items;
    }

    /**
     * @inheritdoc
     */
    public function getUserValue(User $user, $raw = true): ?string
    {
        $internalName = $this->profileField->internal_name;
        $value = $user->profile->$internalName;

        if (!$raw) {
            return Html::encode(Iso3166Codes::country($value));
        }

        return $value;
    }

    /**
     * @inheritdoc
     */
    public function getFieldFormDefinition(User $user = null, array $options = []): array
    {
        return parent::getFieldFormDefinition($user, array_merge([
            'htmlOptions' => ['data-ui-select2' => true, 'style' => 'width:100%']
        ], $options));
    }

}
