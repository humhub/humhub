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
     * The controller route serving the ALTCHA Web Worker. It must resolve to an
     * absolute, same-origin URL (see {@see run()}), because the `Worker`
     * constructor cannot load a script from a cross-origin assets mount (S3/CDN).
     * @since 1.19
     */
    public string $workerAction = '/captcha/worker';
    /**
     * The form input HTML element (e.g. #my-text-field) that needs to be focused to show the Captcha input
     * If empty, the Captcha input is always displayed
     * @since 1.18.1
     */
    public ?string $showOnFocusElement = null;

    /**
     * @inerhitdoc
     * @throws JsonException
     */
    public function run()
    {
        $id = $this->options['id'] ?? null;
        if (!$id) {
            $this->showOnFocusElement = null;
        }

        // Register assets and a JS event to remove the Bootstrap is-invalid class when the captcha is verified
        $view = $this->getView();
        AltchaCaptchaAsset::register($view);
        // prevent HTML5 validation as we do server-side validation with the selected language
        $js = "
            $('altcha-widget').on('verified', (evt) => $(evt.target).removeClass('is-invalid'))
                .find('input[type=\"checkbox\"]')
                .prop('required', false);
        ";
        if ($this->showOnFocusElement) {
            $js .= "
                $(function () {
                    const container = $('#$id').parent();
                    const focusInput = $('$this->showOnFocusElement');
                    if (!$('#$id.is-invalid').length && !focusInput.is(':focus')) {
                        container.hide();
                        focusInput.on('focus', function () {
                            container.fadeIn(500);
                        });
                    }
                });
            ";
        }
        $view->registerJs($js);

        return Html::tag('altcha-widget', '', array_merge([
            'challengeurl' => Url::to([$this->captchaAction]),
            // ALTCHA resolves the worker URL against altcha.js' own location,
            // which may be a cross-origin assets mount (S3/CDN). The Worker
            // constructor only accepts a same-origin script, so force an
            // absolute URL on the application origin.
            'workerurl' => Url::to([$this->workerAction], true),
            'name' => Html::getInputName($this->model, $this->attribute),
            'hidefooter' => true,
            'strings' => json_encode([
                'label' => Yii::t('base', 'I\'m not a robot'),
                'verified' => Yii::t('base', 'Verified'),
            ], JSON_THROW_ON_ERROR),
        ], $this->options));
    }
}
