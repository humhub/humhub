<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2025 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\components\captcha;

use humhub\components\assets\AssetBundle;

/**
 * @since 1.18
 */
class AltchaCaptchaAsset extends AssetBundle
{
    public $sourcePath = '@npm/altcha/dist_external';

    public $css = [
        'altcha.css',
    ];

    public $js = [
        ['altcha.js', 'type' => 'module'],
        'worker.js',
    ];
}
