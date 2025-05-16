<?php

namespace yii\bootstrap;

class BootstrapAsset extends \yii\bootstrap5\BootstrapAsset
{
    /**
     * Remove bootstrap.css as it's already included in theme.css when compiled from SCSS
     */
    public $css = [];
}
