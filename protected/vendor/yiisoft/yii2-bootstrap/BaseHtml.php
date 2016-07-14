<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace yii\bootstrap;

use yii\helpers\ArrayHelper;

/**
 * BaseHtml provides concrete implementation for [[Html]].
 *
 * Do not use BaseHtml. Use [[Html]] instead.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 2.0.5
 */
class BaseHtml extends \yii\helpers\Html
{
    /**
     * Composes icon HTML for bootstrap Glyphicons.
     * @param string $name icon short name, for example: 'star'
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as
     * the attributes of the resulting tag. There are also a special options:
     *
     * - tag: string, tag to be rendered, by default 'span' is used.
     * - prefix: string, prefix which should be used to compose tag class, by default 'glyphicon glyphicon-' is used.
     *
     * @return string icon HTML.
     * @see http://getbootstrap.com/components/#glyphicons
     */
    public static function icon($name, $options = [])
    {
        $tag = ArrayHelper::remove($options, 'tag', 'span');
        $classPrefix = ArrayHelper::remove($options, 'prefix', 'glyphicon glyphicon-');
        static::addCssClass($options, $classPrefix . $name);
        return static::tag($tag, '', $options);
    }

    /**
     * Renders Bootstrap static form control.
     *
     * By default value will be HTML-encoded using [[encode()]], you may control this behavior
     * via 'encode' option.
     * @param string $value static control value.
     * @param array $options the tag options in terms of name-value pairs. These will be rendered as
     * the attributes of the resulting tag. There are also a special options:
     *
     * - encode: boolean, whether value should be HTML-encoded or not.
     *
     * @return string generated HTML
     * @see http://getbootstrap.com/css/#forms-controls-static
     */
    public static function staticControl($value, $options = [])
    {
        static::addCssClass($options, 'form-control-static');
        $value = (string) $value;
        if (isset($options['encode'])) {
            $encode = $options['encode'];
            unset($options['encode']);
        } else {
            $encode = true;
        }
        return static::tag('p', $encode ? static::encode($value) : $value, $options);
    }

    /**
     * Generates a Bootstrap static form control for the given model attribute.
     * @param \yii\base\Model $model the model object.
     * @param string $attribute the attribute name or expression. See [[getAttributeName()]] for the format
     * about attribute expression.
     * @param array $options the tag options in terms of name-value pairs. See [[staticControl()]] for details.
     * @return string generated HTML
     * @see staticControl()
     */
    public static function activeStaticControl($model, $attribute, $options = [])
    {
        if (isset($options['value'])) {
            $value = $options['value'];
            unset($options['value']);
        } else {
            $value = static::getAttributeValue($model, $attribute);
        }
        return static::staticControl($value, $options);
    }
}
