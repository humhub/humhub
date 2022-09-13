<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\web\pwa\widgets;

use humhub\components\Widget;
use humhub\libs\LogoImage;
use humhub\modules\space\models\Space;
use humhub\modules\ui\view\components\View;
use humhub\modules\web\Module;
use Yii;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\imagine\Image;
use yii\web\Request;

/**
 * Class LayoutHeader
 *
 * @package humhub\modules\ui\widgets
 */
class LayoutHeader extends Widget
{
    /**
     * Registers mobile app related Head Tags
     *
     * @param View $view
     * @throws InvalidConfigException
     */
    public static function registerHeadTags(View $view)
    {

        $view->registerMetaTag(['name' => 'theme-color', 'content' => Yii::$app->view->theme->variable('primary')]);
        $view->registerMetaTag(['name' => 'application-name', 'content' => Yii::$app->name]);

        // Apple/IOS headers
        // https://developer.apple.com/library/archive/documentation/AppleApplications/Reference/SafariWebContent/ConfiguringWebApplications/ConfiguringWebApplications.html
        $view->registerMetaTag(['name' => 'apple-mobile-web-app-title', 'content' => Yii::$app->name]);
        $view->registerMetaTag(['name' => 'apple-mobile-web-app-capable', 'content' => 'yes']);
        $view->registerMetaTag(['name' => 'apple-mobile-web-app-status-bar-style', 'content' => Yii::$app->view->theme->variable('primary')]);

        $view->registerLinkTag(['rel' => 'manifest', 'href' => Url::to(['/web/pwa-manifest/index'])]);

        /** @var Module $module */
        $module = Yii::$app->getModule('web');
        if ($module->enableServiceWorker !== false) {
            static::registerServiceWorker($view);
        }

        static::addMetaTags($view);
        static::registerOpenGraphMetaTags($view);
        static::registerTwitterMetaTags($view);
    }

    /**
     * Add metadata to view to preview the content with Open Graph protocol
     * @param View $view
     * @return View
     */
    protected static function addMetaTags(View $view)
    {
        // Type
        $view->metaType = $view->metaType ?: 'website';

        // URL
        $view->metaUrl = $view->metaUrl ?: (Yii::$app->getRequest() instanceof Request ? Yii::$app->getRequest()->getAbsoluteUrl() : null);
        $view->registerLinkTag(['rel' => 'canonical', 'href' => $view->metaUrl]);

        // Date
        try {
            $view->metaDate = $view->metaDate ?: Yii::$app->formatter->asDatetime(time());
        } catch (InvalidConfigException $e) {
        }

        // Image
        $imageUrl = $view->metaImage;
        // Try to get space image
        if (
            !$imageUrl
            && isset($view->context->contentContainer)
            && $view->context->contentContainer instanceof Space
        ) {
            /** @var Space $space */
            $space = $view->context->contentContainer;
            $image = $space->getProfileImage();
            try {
                if (file_exists($image->getPath('_org'))) {
                    $originalImage = Image::getImagine()->open($image->getPath('_org'));
                    if ($originalImage && $originalImage->getSize()->getHeight() > 200 && $originalImage->getSize()->getWidth() > 200) { // 200px is the minimum size for Facebook
                        $imageUrl = $image->getUrl('_org');
                    }
                }
            } catch (Exception $e) {
            }
        }
        // Else, get logo image
        $view->metaImage = Url::to($imageUrl ?: LogoImage::getUrl(1000, 250), true);

        // Title
        $view->metaTitle = $view->metaTitle ?: $view->title;

        // Description
        if ($view->metaDescription) {
            $view->registerMetaTag(['name' => 'description', 'content' => $view->metaDescription]);
        }

        return $view;
    }

    /**
     * Register metadata to preview the content on Facebook and other compatible websites
     * @param View $view
     * @return void
     */
    protected static function registerOpenGraphMetaTags(View $view)
    {
        $view->registerMetaTag(['property' => 'og:title', 'content' => $view->metaTitle]);
        $view->registerMetaTag(['property' => 'og:url', 'content' => $view->metaUrl]);
        $view->registerMetaTag(['property' => 'og:site_name', 'content' => Yii::$app->name]);
        $view->registerMetaTag(['property' => 'og:type', 'content' => $view->metaType]);
        $view->registerMetaTag(['property' => 'og:description', 'content' => $view->metaDescription]);
        $view->registerMetaTag(['property' => 'og:updated_time', 'content' => $view->metaDate]);

        if ($view->metaImage !== null) {
            if (is_array($view->metaImage)) {
                foreach ($view->metaImage as $key => $value) {
                    $view->registerMetaTag(['property' => 'og:image', 'content' => $value], 'og:image' . $key);
                }
            } else {
                $view->registerMetaTag(['property' => 'og:image', 'content' => $view->metaImage], 'og:image');
            }
        }
    }


    /**
     * Register metadata to preview the content on Twitter
     * @param View $view
     * @return void
     */
    protected static function registerTwitterMetaTags(View $view)
    {
        $view->registerMetaTag(['property' => 'twitter:title', 'content' => $view->metaTitle]);
        $view->registerMetaTag(['property' => 'twitter:url', 'content' => $view->metaUrl]);
        $view->registerMetaTag(['property' => 'twitter:site', 'content' => Yii::$app->name]);
        $view->registerMetaTag(['property' => 'twitter:type', 'content' => $view->metaType]);
        $view->registerMetaTag(['property' => 'twitter:description', 'content' => $view->metaDescription]);

        if ($view->metaImage !== null) {
            $view->registerMetaTag(['name' => 'twitter:image', 'content' => (is_array($view->metaImage) ? reset($view->metaImage) : $view->metaImage)], 'twitter:image');
        }
    }


    private static function registerServiceWorker(View $view)
    {
        $cacheId = Yii::$app->cache->getOrSet('service-worker-cache-id', function () {
            return time();
        });
        $serviceWorkUrl = Url::to(['/web/pwa-service-worker/index', 'v' => $cacheId]);
        $rootPath = Yii::getAlias('@web') . '/';

        $view->registerJs(<<<JS
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('$serviceWorkUrl', { scope: '$rootPath' })
                    .then(function (registration) {
                        if (typeof afterServiceWorkerRegistration === "function") {
                            afterServiceWorkerRegistration(registration);
                        }
                    })
            }
JS
            , View::POS_READY, 'serviceWorkerInit');

    }

}
