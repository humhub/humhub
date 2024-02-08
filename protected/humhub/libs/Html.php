<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\libs;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\ui\icon\widgets\Icon;
use humhub\modules\user\models\User;
use humhub\modules\web\security\helpers\Security;
use Yii;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;

/**
 * HTML Helpers
 *
 * @since 1.2
 * @author Luke
 */
class Html extends \yii\bootstrap\Html
{
    /**
     * @param string $content
     * @param array $options
     * @return string
     */
    public static function script($content, $options = [])
    {
        static::setNonce($options);
        return parent::script($content, $options);
    }

    /**
     * @return string
     * @throws \Exception
     * @since 1.4
     */
    public static function nonce()
    {
        $nonce = Security::getNonce();
        return $nonce ? 'nonce="' . $nonce . '"' : '';
    }

    /**
     * {@inheritDoc}
     */
    public static function beginTag($name, $options = [])
    {
        if ($name === 'script') {
            static::setNonce($options);
        }

        return parent::beginTag($name, $options);
    }

    /**
     * @return string
     * @since 1.4
     */
    public static function setNonce(&$options = [])
    {
        $nonce = Security::getNonce();

        if ($nonce) {
            $options['nonce'] = $nonce;
        }
    }

    /**
     * Save button is a shortcut for the default submit button
     *
     * @param string $label
     * @param array $options
     * @return string the button
     * @see submitButton
     * @since 1.2
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
     * @param string $text
     * @param string $url
     * @param array $options
     * @return string the back button
     * @since 1.2
     * @see Html::a
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
            $label = Icon::get('back')->asString() . ' ' . $label;
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
     * @param ContentContainerActiveRecord $container the content container
     * @param array $options the html options
     * @return string the generated html a tag
     * @todo More flexible implemenation using interfaces
     * @since 1.2
     */
    public static function containerLink(ContentContainerActiveRecord $container, $options = [])
    {
        $options['data-contentcontainer-id'] = $container->contentcontainer_id;
        $options['data-guid'] = $container->guid;

        if ($container instanceof Space) {
            return static::a(static::encode($container->name), $container->getUrl(), $options);
        } elseif ($container instanceof User) {
            if ($container->status == User::STATUS_SOFT_DELETED) {
                return static::beginTag('strike') . static::encode($container->displayName) . static::endTag('strike');
            }
            return static::a(static::encode($container->displayName), $container->getUrl(), $options);
        } else {
            throw new InvalidArgumentException('Content container type not supported!');
        }
    }

    /**
     * @param $options
     * @return bool
     * @since 1.3
     */
    public static function addPjaxPrevention(&$options)
    {
        $options['data-pjax-prevent'] = 1;
    }

    /**
     * @param $options
     * @return bool
     * @since 1.4
     */
    public static function isPjaxEnabled($options)
    {
        if (empty($options)) {
            return false;
        }

        if (isset($options['data-pjax-prevent'])) {
            return $options['data-pjax-prevent'] !== false;
        }

        return false;
    }

    /**
     * Adds a tooltip to the given options array.
     * Note, this will overwrite the title attribute.
     *
     * @param $options
     * @param $tooltip
     */
    public static function addTooltip(&$options, $tooltip)
    {
        static::addCssClass($options, 'tt');
        $options['title'] = $tooltip;
    }

    /**
     * Starts a Bootstrap container tag.
     *
     * @param $fluid null|boolean if null fluid will be used if supported by active theme
     * @param $options
     *
     * @return string
     */
    public static function beginContainer($fluid = null, $options = [])
    {
        $isFluid = (($fluid === null || $fluid === true) && ThemeHelper::isFluid());

        return static::beginTag('div', array_merge(['class' => ($isFluid) ? 'container-fluid' : 'container'], $options));
    }


    /**
     * Ends the bootstrap container tag
     *
     * @return string
     */
    public static function endContainer()
    {
        return static::endTag('div');
    }

    public static function getDropDownListOptions(array $options = []): array
    {
        if (isset($options['minimumResultsForSearch'])) {
            $minimumResultsForSearch = (int) $options['minimumResultsForSearch'];
            unset($options['minimumResultsForSearch']);
        } else {
            $minimumResultsForSearch = 5;
        }

        if ($minimumResultsForSearch >= 0 && isset($options['prompt'])) {
            // Don't consider an empty option like "Please select:" as real option for searching
            $minimumResultsForSearch++;
        }

        if ($minimumResultsForSearch > 0 && isset($options['data-ui-select2-allow-new'])) {
            // Don't use this limit when new item is allowed for adding,
            // otherwise a new item input is hidden by Select2 JS code
            $minimumResultsForSearch = 0;
        }

        return ArrayHelper::merge([
            'data-ui-select2' => true,
            'style' => 'width:100%',
            'data-search-input-placeholder' => Yii::t('base', 'Search...'),
            'data-minimum-results-for-search' => $minimumResultsForSearch,
        ], $options);
    }

    /**
     * Override Active drop-down list to enable plugin Select2 with
     *     searchable feature if items >= $options['minimumResultsForSearch'],
     *     -1 - to never display the search box,
     *      0 - always display the search box.
     * @inheritdoc
     */
    public static function activeDropDownList($model, $attribute, $items, $options = [])
    {
        return parent::activeDropDownList($model, $attribute, $items, self::getDropDownListOptions($options));
    }

    /**
     * Override drop-down list to enable plugin Select2 with
     *     searchable feature if items >= $options['minimumResultsForSearch'],
     *     -1 - to never display the search box,
     *      0 - always display the search box.
     * @inheritdoc
     */
    public static function dropDownList($name, $selection = null, $items = [], $options = [])
    {
        return parent::dropDownList($name, $selection, $items, self::getDropDownListOptions($options));
    }

}
