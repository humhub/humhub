<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2016 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;

/**
 * HTML Helpers
 *
 * @since 1.2
 * @author Luke
 */
class Html extends \yii\bootstrap\Html
{

    /**
     * Save button is a shortcut for the default submit button 
     * 
     * @since 1.2
     * @see submitButton
     * @param string $label
     * @param array $options
     * @return string the button
     */
    public static function saveButton($label = '', $options = [])
    {
        if ($label === '') {
            $label = Yii::t('base', 'Save');
        }

        if (!isset($options['class'])) {
            $options['class'] = 'btn btn-primary';
        }
        $options['data-ui-loader'] = '';

        return parent::submitButton($label, $options);
    }

    /**
     * Renders a back button
     * 
     * @since 1.2
     * @see Html::a
     * @param string $text
     * @param string $url
     * @param array $options
     * @return string the back button
     */
    public static function backButton($url = '', $options = [])
    {
        $label = '';

        if (!isset($options['label'])) {
            $label = Yii::t('base', 'Back');
        } else {
            $label = $options['label'];
        }

        if (!isset($options['showIcon']) || $options['showIcon'] === true) {
            $label = '<i class="fa fa-arrow-left aria-hidden="true"></i> ' . $label;
        }

        if (empty($url)) {
            $url = 'javascript:history.back()';
        }

        $options['data-ui-loader'] = '';

        if (!isset($options['class'])) {
            $options['class'] = '';
        }

        $options['class'] .= ' btn btn-default';

        return parent::a($label, $url, $options);
    }

}
