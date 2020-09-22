<?php


namespace humhub\components\assets;

/**
 * Base asset bundle class for @web-static assets residing in `static` directory.
 *
 * @package humhub\components\assets
 */
class WebStaticAssetBundle extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public $basePath = '@webroot-static';

    /**
     * @inheritdoc
     */
    public $baseUrl = '@web-static';

}
