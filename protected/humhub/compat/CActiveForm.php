<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\compat;

use \humhub\compat\CHtml;

/**
 * CActiveForm is a Yii1 compatible active form
 *
 * @author luke
 */
class CActiveForm extends \yii\widgets\ActiveForm
{

    public function labelEx($model, $attribute, $htmlOptions = array())
    {
        return CHtml::activeLabelEx($model, $attribute, $htmlOptions);
    }

    public function error($model, $attribute, $htmlOptions = array(), $enableAjaxValidation = true, $enableClientValidation = true)
    {
        return CHtml::error($model, $attribute, $htmlOptions);
    }

    public function passwordField($model, $attribute, $htmlOptions = array())
    {
        return CHtml::activePasswordInput($model, $attribute, $htmlOptions);
    }

    public function textArea($model, $attribute, $htmlOptions = array())
    {
        return CHtml::activeTextArea($model, $attribute, $htmlOptions);
    }

    public function checkBox($model, $attribute, $htmlOptions = array())
    {
        return CHtml::activeCheckBox($model, $attribute, $htmlOptions);
    }

    public function dropDownList($model, $attribute, $data, $htmlOptions = array())
    {
        return CHtml::activeDropDownList($model, $attribute, $data, $htmlOptions);
    }

    public function textField($model, $attribute, $htmlOptions = array())
    {
        return CHtml::activeTextField($model, $attribute, $htmlOptions);
    }

    public function fileField($model, $attribute, $htmlOptions = array())
    {
        return CHtml::activeFileInput($model, $attribute, $htmlOptions);
    }

    public function hiddenField($model, $attribute, $htmlOptions = array())
    {
        return CHtml::activeHiddenInput($model, $attribute, $htmlOptions);
    }

}
