<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\models\fieldtype;

use humhub\modules\user\models\User;
use Yii;
use yii\helpers\Url;

/**
 * ProfileFieldTypeTextAutocomplete handles text profile fields with autocomplete option.
 */
class TextAutocomplete extends Text
{
    /**
     * Return the Form Element to edit the value of the Field
     * @return array
     */
    public function getFieldFormDefinition()
    {
        return [
            $this->profileField->internal_name => [
                'type' => 'text-autocomplete',
                'readonly' => (!$this->profileField->editable),
                'htmlOptions' => [
                    'itemTextKey' => $this->profileField->internal_name,
                    'options' => [
                        'class' => 'form-control',
                        'data-toggle' => 'dropdown'
                    ]
                ]
            ]
        ];
    }
}
