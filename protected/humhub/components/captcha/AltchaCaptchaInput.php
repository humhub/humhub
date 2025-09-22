<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\captcha;

use humhub\helpers\Html;
use JsonException;
use Yii;
use yii\helpers\Url;
use yii\widgets\InputWidget;

/**
 * @since 1.18
 */
class AltchaCaptchaInput extends InputWidget
{
    public string $captchaAction = '/captcha/altcha';

    /**
     * @inerhitdoc
     * @throws JsonException
     */
    public function run()
    {
        // Register assets and a JS event to remove the Bootstrap is-invalid class when the captcha is verified
        $view = $this->getView();
        AltchaCaptchaAsset::register($view);
        $view->registerJs("$('altcha-widget').on('verified', (evt) => $(evt.target).removeClass('is-invalid'));");

        // Options list: https://altcha.org/docs/v2/widget-integration/#configuration
        return Html::tag('altcha-widget', '', array_merge([
            'challengeurl' => Url::to([$this->captchaAction]),
            'name' => Html::getInputName($this->model, $this->attribute),
            'hidefooter' => true,
            'strings' => json_encode([
                'label' => Yii::t('base', 'I\'m not a robot'),
                'verified' => Yii::t('base', 'Verified'),
            ], JSON_THROW_ON_ERROR),
        ], $this->options));
    }
}
