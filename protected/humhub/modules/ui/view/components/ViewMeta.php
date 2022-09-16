<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\view\components;

use humhub\libs\LogoImage;
use humhub\modules\space\models\Space;
use Yii;
use yii\base\BaseObject;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\Url;
use yii\imagine\Image;
use yii\web\Request;


class ViewMeta extends BaseObject
{
    /**
     * The type metadata to preview the content with Open Graph protocol
     * @var string
     */
    public $metaType;

    /**
     * The URL metadata to preview the content with Open Graph protocol
     * @var string
     */
    public $metaUrl;

    /**
     * The date metadata to preview the content with Open Graph protocol
     * @var string
     */
    public $metaDate;

    /**
     * The image metadata to preview the content with Open Graph protocol
     * @var string
     */
    public $metaImage;

    /**
     * The title metadata to preview the content with Open Graph protocol
     * @var string
     */
    public $metaTitle;

    /**
     * The description metadata to preview the content with Open Graph protocol
     * @var string
     */
    public $metaDescription;

    public function registerMetaTags(View $view)
    {
        $this->addMetaTags($view);
        $this->registerOpenGraphMetaTags($view);
        $this->registerTwitterMetaTags($view);
    }


    /**
     * Add metadata to view to preview the content with Open Graph protocol
     *
     * @param View $view
     * @return View
     */
    private function addMetaTags(View $view)
    {
        // Type
        $this->metaType = $this->metaType ?: 'website';

        // URL
        $this->metaUrl = $this->metaUrl ?: (Yii::$app->getRequest() instanceof Request ? Yii::$app->getRequest()->getAbsoluteUrl() : null);
        $view->registerLinkTag(['rel' => 'canonical', 'href' => $this->metaUrl]);

        // Date
        try {
            $this->metaDate = $this->metaDate ?: Yii::$app->formatter->asDatetime(time());
        } catch (InvalidConfigException $e) {
        }

        // Image
        $imageUrl = $this->metaImage;
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
        $this->metaImage = Url::to($imageUrl ?: LogoImage::getUrl(1000, 250), true);

        // Title
        $this->metaTitle = $this->metaTitle ?: $view->title;

        // Description
        if ($this->metaDescription) {
            $view->registerMetaTag(['name' => 'description', 'content' => $this->metaDescription]);
        }

        return $view;
    }

    /**
     * Register metadata to preview the content on Facebook and other compatible websites
     * @param View $view
     * @return void
     */
    private function registerOpenGraphMetaTags(View $view)
    {
        $view->registerMetaTag(['property' => 'og:title', 'content' => $this->metaTitle]);
        $view->registerMetaTag(['property' => 'og:url', 'content' => $this->metaUrl]);
        $view->registerMetaTag(['property' => 'og:site_name', 'content' => Yii::$app->name]);
        $view->registerMetaTag(['property' => 'og:type', 'content' => $this->metaType]);
        $view->registerMetaTag(['property' => 'og:description', 'content' => $this->metaDescription]);
        $view->registerMetaTag(['property' => 'og:updated_time', 'content' => $this->metaDate]);

        if ($this->metaImage !== null) {
            if (is_array($this->metaImage)) {
                foreach ($this->metaImage as $key => $value) {
                    $view->registerMetaTag(['property' => 'og:image', 'content' => $value], 'og:image' . $key);
                }
            } else {
                $view->registerMetaTag(['property' => 'og:image', 'content' => $this->metaImage], 'og:image');
            }
        }
    }


    /**
     * Register metadata to preview the content on Twitter
     * @param View $view
     * @return void
     */
    private function registerTwitterMetaTags(View $view)
    {
        $view->registerMetaTag(['property' => 'twitter:title', 'content' => $this->metaTitle]);
        $view->registerMetaTag(['property' => 'twitter:url', 'content' => $this->metaUrl]);
        $view->registerMetaTag(['property' => 'twitter:site', 'content' => Yii::$app->name]);
        $view->registerMetaTag(['property' => 'twitter:type', 'content' => $this->metaType]);
        $view->registerMetaTag(['property' => 'twitter:description', 'content' => $this->metaDescription]);

        if ($this->metaImage !== null) {
            $view->registerMetaTag([
                'name' => 'twitter:image', 'content' => (
                is_array($this->metaImage) ?
                    reset($this->metaImage) : $this->metaImage
                )
            ], 'twitter:image');
        }
    }

}
