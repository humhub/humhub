<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\compat;

/**
 * CActiveForm is a Yii 1 compatible active form
 *
 * @author luke
 */
class CActiveForm extends \yii\widgets\ActiveForm
{
    public function label($model, $attribute, $htmlOptions = [])
    {
        return CHtml::activeLabel($model, $attribute, $htmlOptions);
    }

    public function labelEx($model, $attribute, $htmlOptions = [])
    {
        return CHtml::activeLabelEx($model, $attribute, $htmlOptions);
    }

    public function error($model, $attribute, $htmlOptions = [], $enableAjaxValidation = true, $enableClientValidation = true)
    {
        return CHtml::error($model, $attribute, $htmlOptions);
    }

    public function passwordField($model, $attribute, $htmlOptions = [])
    {
        return CHtml::activePasswordInput($model, $attribute, $htmlOptions);
    }

    public function textArea($model, $attribute, $htmlOptions = [])
    {
        return CHtml::activeTextarea($model, $attribute, $htmlOptions);
    }

    public function checkBox($model, $attribute, $htmlOptions = [])
    {
        return CHtml::activeCheckboxNoLabel($model, $attribute, $htmlOptions);
    }

    public function dropDownList($model, $attribute, $data, $htmlOptions = [])
    {
        return CHtml::activeDropDownList($model, $attribute, $data, $htmlOptions);
    }

    public function radioButtonList($model, $attribute, $data, $htmlOptions = [])
    {
        return CHtml::activeRadioList($model, $attribute, $data, $htmlOptions);
    }

    public function radioButton($model, $attribute, $options = [])
    {
        $name = isset($options['name']) ? $options['name'] : CHtml::getInputName($model, $attribute);
        $value = CHtml::getAttributeValue($model, $attribute);

        if (!array_key_exists('value', $options)) {
            $options['value'] = '1';
        }

        $checked = "$value" === "{$options['value']}";

        if (!array_key_exists('id', $options)) {
            $options['id'] = CHtml::getInputId($model, $attribute);
        }

        return CHtml::radio($name, $checked, $options);
    }

    public function textField($model, $attribute, $htmlOptions = [])
    {
        return CHtml::activeTextField($model, $attribute, $htmlOptions);
    }

    public function fileField($model, $attribute, $htmlOptions = [])
    {
        return CHtml::activeFileInput($model, $attribute, $htmlOptions);
    }

    public function hiddenField($model, $attribute, $htmlOptions = [], $value=null)
    {
        if ($value !== null) {
            $model->$attribute = $value;
        }

        return CHtml::activeHiddenInput($model, $attribute, $htmlOptions);
    }
}
