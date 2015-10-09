<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\compat;

/**
 * CHtml - Yii1 compatiblity
 *
 * @author luke
 */
class CHtml extends \yii\helpers\Html
{

    public static function form($action, $method = "POST")
    {
        return self::beginForm($action, $method);
    }

    public static function hiddenField($name, $value)
    {
        return self::hiddenInput($name, $value);
    }

    public function ajaxButton()
    {
        return "";
    }

    /**
     * Generates a label tag for a model attribute.
     * This is an enhanced version of {@link activeLabel}. It will render additional
     * CSS class and mark when the attribute is required.
     * In particular, it calls {@link CModel::isAttributeRequired} to determine
     * if the attribute is required.
     * If so, it will add a CSS class {@link CHtml::requiredCss} to the label,
     * and decorate the label with {@link CHtml::beforeRequiredLabel} and
     * {@link CHtml::afterRequiredLabel}.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions additional HTML attributes.
     * @return string the generated label tag
     */
    public static function activeLabelEx($model, $attribute, $htmlOptions = array())
    {
        $realAttribute = $attribute;
        self::resolveName($model, $attribute); // strip off square brackets if any
        $htmlOptions['required'] = $model->isAttributeRequired($attribute);
        return self::activeLabel($model, $realAttribute, $htmlOptions);
    }

    /**
     * Generates a text field input for a model attribute.
     * If the attribute has input error, the input field's CSS class will
     * be appended with {@link errorCss}.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions additional HTML attributes. Besides normal HTML attributes, a few special
     * attributes are also recognized (see {@link clientChange} and {@link tag} for more details.)
     * @return string the generated input field
     * @see clientChange
     * @see activeInputField
     */
    public static function activeTextField($model, $attribute, $htmlOptions = array())
    {
        self::resolveNameID($model, $attribute, $htmlOptions);
        #self::clientChange('change', $htmlOptions);
        return self::activeInput('text', $model, $attribute, $htmlOptions);
    }

    /**
     * Generates input name for a model attribute.
     * Note, the attribute name may be modified after calling this method if the name
     * contains square brackets (mainly used in tabular input) before the real attribute name.
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @return string the input name
     */
    public static function resolveName($model, &$attribute)
    {
        $modelName = self::modelName($model);

        if (($pos = strpos($attribute, '[')) !== false) {
            if ($pos !== 0)  // e.g. name[a][b]
                return $modelName . '[' . substr($attribute, 0, $pos) . ']' . substr($attribute, $pos);
            if (($pos = strrpos($attribute, ']')) !== false && $pos !== strlen($attribute) - 1) {  // e.g. [a][b]name
                $sub = substr($attribute, 0, $pos + 1);
                $attribute = substr($attribute, $pos + 1);
                return $modelName . $sub . '[' . $attribute . ']';
            }
            if (preg_match('/\](\w+\[.*)$/', $attribute, $matches)) {
                $name = $modelName . '[' . str_replace(']', '][', trim(strtr($attribute, array('][' => ']', '[' => ']')), ']')) . ']';
                $attribute = $matches[1];
                return $name;
            }
        }
        return $modelName . '[' . $attribute . ']';
    }

    /**
     * Generates input name and ID for a model attribute.
     * This method will update the HTML options by setting appropriate 'name' and 'id' attributes.
     * This method may also modify the attribute name if the name
     * contains square brackets (mainly used in tabular input).
     * @param CModel $model the data model
     * @param string $attribute the attribute
     * @param array $htmlOptions the HTML options
     */
    public static function resolveNameID($model, &$attribute, &$htmlOptions)
    {
        if (!isset($htmlOptions['name']))
            $htmlOptions['name'] = self::resolveName($model, $attribute);
        if (!isset($htmlOptions['id']))
            $htmlOptions['id'] = self::getIdByName($htmlOptions['name']);
        elseif ($htmlOptions['id'] === false)
            unset($htmlOptions['id']);
    }

    /**
     * Generates a valid HTML ID based on name.
     * @param string $name name from which to generate HTML ID
     * @return string the ID generated based on name.
     */
    public static function getIdByName($name)
    {
        return str_replace(array('[]', '][', '[', ']', ' '), array('', '_', '_', '', '_'), $name);
    }

    /**
     * Generates HTML name for given model.
     * @see CHtml::setModelNameConverter()
     * @param CModel|string $model the data model or the model class name
     * @return string the generated HTML name value
     * @since 1.1.14
     */
    public static function modelName($model)
    {
        return $model->formName();
    }

    /**
     * Active Checkbox without Label
     * 
     * @param type $model
     * @param type $attribute
     * @param type $options
     * @return type
     */
    public static function activeCheckboxNoLabel($model, $attribute, $options = [])
    {
        $name = isset($options['name']) ? $options['name'] : static::getInputName($model, $attribute);
        $value = static::getAttributeValue($model, $attribute);

        if (!array_key_exists('value', $options)) {
            $options['value'] = '1';
        }
        if (!array_key_exists('uncheck', $options)) {
            $options['uncheck'] = '0';
        }

        $checked = "$value" === "{$options['value']}";

        if (!array_key_exists('id', $options)) {
            $options['id'] = static::getInputId($model, $attribute);
        }

        return static::checkbox($name, $checked, $options);
    }

}
