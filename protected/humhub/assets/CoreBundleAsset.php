<?php

namespace humhub\assets;

use humhub\components\assets\WebStaticAssetBundle;
use humhub\modules\activity\assets\ActivityAsset;
use humhub\modules\comment\assets\CommentAsset;
use humhub\modules\content\assets\ContentAsset;
use humhub\modules\content\assets\ContentContainerAsset;
use humhub\modules\content\assets\ProseMirrorRichTextAsset;
use humhub\modules\file\assets\FileAsset;
use humhub\modules\like\assets\LikeAsset;
use humhub\modules\live\assets\LiveAsset;
use humhub\modules\notification\assets\NotificationAsset;
use humhub\modules\post\assets\PostAsset;
use humhub\modules\space\assets\SpaceAsset;
use humhub\modules\space\assets\SpaceChooserAsset;
use humhub\modules\stream\assets\StreamAsset;
use humhub\modules\topic\assets\TopicAsset;
use humhub\modules\ui\filter\assets\FilterAsset;
use humhub\modules\user\assets\UserAsset;
use humhub\modules\user\assets\UserPickerAsset;

/**
 * This asset bundle contains core script dependencies which should be compatible with defer script loading.
 * In a production build, all scripts will be bundled within `static/js/humhub-bundle.js` and deally be loaded with
 * defer script loading.
 *
 * > Note: this class should not depend on any style assets, otherwise an extra humhub-bundle.css will be created which
 * will triggers an extra asset request. All core style assets should be part of the `AppAsset` class.
 */
class CoreBundleAsset extends WebStaticAssetBundle
{
    const BUNDLE_NAME = 'defer';

    public $defaultDepends = false;

    const STATIC_DEPENDS = [
        AppAsset::class,
        JqueryHighlightAsset::class,
        JqueryAutosizeAsset::class,
        Select2Asset::class,
        JqueryWidgetAsset::class,
        NProgressAsset::class,
        JqueryNiceScrollAsset::class,
        BlueimpFileUploadAsset::class,
        BlueimpGalleryAsset::class,
        ClipboardJsAsset::class,
        ImagesLoadedAsset::class,
        HighlightJsAsset::class,
        SwipedEventsAssets::class,
        CoreExtensionAsset::class,
        ProsemirrorEditorAsset::class,
        ProseMirrorRichTextAsset::class,
        JqueryCookieAsset::class,
        UserAsset::class,
        LiveAsset::class,
        NotificationAsset::class,
        ContentContainerAsset::class,
        UserPickerAsset::class,
        PostAsset::class,
        SpaceAsset::class,
        TopicAsset::class,
        FilterAsset::class,
        CommentAsset::class,
        LikeAsset::class,
        StreamAsset::class,
        ActivityAsset::class,
        SpaceChooserAsset::class
    ];

    public $js = [
        'js/humhub/legacy/jquery.loader.js'
    ];

    public $depends = self::STATIC_DEPENDS;
}
