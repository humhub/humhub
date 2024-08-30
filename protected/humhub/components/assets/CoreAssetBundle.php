<?php

namespace humhub\components\assets;
use humhub\modules\ui\view\components\View;

/**
 * This asset bundle is for core assets only
 * Don't use it for non-core modules (use AssetBundle instead)
 * It loads core assets before module assets (at View::POS_HEAD_BEGIN)
 */
class CoreAssetBundle extends AssetBundle
{
    /**
     * @ineritdoc
     */
    public $jsPosition = View::POS_HEAD_BEGIN;
}
