<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\captcha;

use Yii;
use yii\captcha\Captcha;

/**
 * @since 1.18
 */
class YiiCaptchaInput extends Captcha
{
    public $captchaAction = '/captcha/yii';
    /**
     * The form input HTML element (e.g. #my-text-field) that needs to be focused to show the Captcha input
     * If empty, the Captcha input is always displayed
     * @since 1.18.1
     */
    public ?string $showOnFocusElement = null;

    public function init()
    {
        $this->options['placeholder'] = Yii::t('base', 'Enter security code above');
        parent::init();

        $id = $this->options['id'] ?? null;
        if (!$id) {
            $this->showOnFocusElement = null;
        }

        if ($this->showOnFocusElement) {
            $view = $this->getView();
            $view->registerJs("
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
            ");
        }
    }
}
