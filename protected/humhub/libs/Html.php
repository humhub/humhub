<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use Yii;
use yii\base\InvalidParamException;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;

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

    /**
     * Generates an link tag to a content container
     * 
     * @since 1.2
     * @todo More flexible implemenation using interfaces
     * @param ContentContainerActiveRecord $container the content container
     * @param array $options the html options
     * @return string the generated html a tag
     */
    public static function containerLink(ContentContainerActiveRecord $container, $options = [])
    {
        if ($container instanceof Space) {
            return static::a(static::encode($container->name), $container->getUrl(), $options);
        } elseif ($container instanceof User) {
            if ($container->status == User::STATUS_SOFT_DELETED) {
                return static::beginTag('strike') . static::encode($container->displayName) . static::endTag('strike');
            }
            return static::a(static::encode($container->displayName), $container->getUrl(), $options);
        } else {
            throw new InvalidParamException('Content container type not supported!');
        }
    }

}
