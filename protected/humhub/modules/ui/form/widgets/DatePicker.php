<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\form\widgets;

use Yii;
use yii\helpers\FormatConverter;
use yii\helpers\Json;
use yii\jui\DatePicker as BaseDatePicker;
use humhub\libs\Html;
use yii\jui\DatePickerLanguageAsset;
use yii\jui\JuiAsset;

/**
 * DatePicker form field widget
 *
 * @since 1.3.0
 * @inheritdoc
 * @package humhub\modules\ui\form\widgets
 */
class DatePicker extends BaseDatePicker
{
    const LANGUAGEMAPPING = [
        'nb-NO' => 'nb',
        'nn-NO' => 'nn',
        'fa-IR' => 'fa',
        'an' => null,
        'uz' => null,
        'ht' => null,
        'am' => null,
    ];

    public $pickerLanguage;

    /**
     * @inheritdoc
     */
    public function init()
    {
        if ($this->dateFormat === null) {
            $this->dateFormat = Yii::$app->formatter->dateInputFormat;
        }

        Html::addCssClass($this->options, 'form-control');

        parent::init();

        $this->pickerLanguage = $this->language ? $this->language : Yii::$app->language;
        $this->pickerLanguage = (array_key_exists($this->pickerLanguage, static::LANGUAGEMAPPING))
            ? static::LANGUAGEMAPPING[$this->pickerLanguage]
            : $this->pickerLanguage;

        if(!$this->pickerLanguage) {
            $this->pickerLanguage = 'en-US';
        }

        $this->pickerLanguage = str_replace('_', '-', $this->pickerLanguage);
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        /**
         * HUMHUB PATCH: Language Mapping + Prevent loading language files for all english based languages, since DatePickerLanguageAsset tries
         * to load a fallback language e.g. for `en-GB` -> 'en'.
         */

        echo $this->renderWidget() . "\n";

        $containerID = $this->inline ? $this->containerOptions['id'] : $this->options['id'];
        $language = $this->language ? $this->language : Yii::$app->language;

        if (strncmp($this->dateFormat, 'php:', 4) === 0) {
            $this->clientOptions['dateFormat'] = FormatConverter::convertDatePhpToJui(substr($this->dateFormat, 4));
        } else {
            $this->clientOptions['dateFormat'] = FormatConverter::convertDateIcuToJui($this->dateFormat, 'date', $language);
        }

        if ($this->pickerLanguage !== 'en-US' && $this->pickerLanguage !== 'en') {
            $view = $this->getView();

            $assetBundle = DatePickerLanguageAsset::register($view);
            if(substr($this->pickerLanguage, 0 , 2) === 'en') {
                $assetBundle->autoGenerate = false;
                $assetBundle->js[] = "ui/i18n/datepicker-$this->pickerLanguage.js";
            } else {
                $assetBundle->language = $this->pickerLanguage;
            }

            $options = Json::htmlEncode($this->clientOptions);
            $this->pickerLanguage = Html::encode($this->pickerLanguage);
            $view->registerJs("jQuery('#{$containerID}').datepicker($.extend({}, $.datepicker.regional['{$this->pickerLanguage}'], $options));");
        } else {
            $this->registerClientOptions('datepicker', $containerID);
        }

        $this->registerClientEvents('datepicker', $containerID);
        JuiAsset::register($this->getView());
    }

}
