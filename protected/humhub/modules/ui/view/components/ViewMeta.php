<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\view\components;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\file\models\File;
use Yii;
use yii\base\BaseObject;
use yii\base\InvalidConfigException;
use yii\web\Request;


class ViewMeta extends BaseObject
{
    /**
     * @var ContentActiveRecord
     */
    private $content;


    /**
     * The type metadata to preview the content with Open Graph protocol
     * @var string
     */
    private $contentType;

    /**
     * The URL metadata to preview the content with Open Graph protocol
     * @var string
     */
    private $url;

    /**
     * The date metadata to preview the content with Open Graph protocol
     * @var string
     */
    private $lastUpdateAt;

    /**
     * @var File[]
     */
    private $images;

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    public $description;

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
        if (empty($this->title)) {
            $this->title = $view->getPageTitle();
        }

        $this->contentType = $this->contentType ?: 'website';

        $this->url = $this->url ?: (Yii::$app->getRequest() instanceof Request ? Yii::$app->getRequest()->getAbsoluteUrl() : null);
        $view->registerLinkTag(['rel' => 'canonical', 'href' => $this->url]);

        if (!empty($this->description)) {
            $view->registerMetaTag(['name' => 'description', 'content' => str_replace("\n", '', $this->description)]);
        }

        try {
            $this->lastUpdateAt = $this->lastUpdateAt ?: Yii::$app->formatter->asDatetime(time());
        } catch (InvalidConfigException $e) {
        }

        // Image

        /*
        $imageUrl = $this->images;
        if (!$imageUrl && isset($view->context->contentContainer)  && $view->context->contentContainer instanceof Space
        ) {
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
        $this->images = Url::to($imageUrl ?: LogoImage::getUrl(1000, 250), true);
        */

        return $view;
    }

    /**
     * Register metadata to preview the content on Facebook and other compatible websites
     * @param View $view
     * @return void
     */
    private function registerOpenGraphMetaTags(View $view)
    {
        $view->registerMetaTag(['property' => 'og:title', 'content' => $this->title]);
        $view->registerMetaTag(['property' => 'og:url', 'content' => $this->url]);
        $view->registerMetaTag(['property' => 'og:site_name', 'content' => Yii::$app->name]);
        $view->registerMetaTag(['property' => 'og:type', 'content' => $this->contentType]);
        if (!empty($this->description)) {
            $view->registerMetaTag(['property' => 'og:description', 'content' => $this->description]);
        }
        if (!empty($this->lastUpdateAt)) {
            $view->registerMetaTag(['property' => 'og:updated_time', 'content' => $this->lastUpdateAt]);
        }

        /*
        if ($this->images !== null) {
            if (is_array($this->images)) {
                foreach ($this->images as $key => $value) {
                    $view->registerMetaTag(['property' => 'og:image', 'content' => $value], 'og:image' . $key);
                }
            } else {
                $view->registerMetaTag(['property' => 'og:image', 'content' => $this->images], 'og:image');
            }
        }
        */
    }


    /**
     * Register metadata to preview the content on Twitter
     * @param View $view
     * @return void
     */
    private function registerTwitterMetaTags(View $view)
    {
        $view->registerMetaTag(['property' => 'twitter:title', 'content' => $this->title]);
        $view->registerMetaTag(['property' => 'twitter:url', 'content' => $this->url]);
        $view->registerMetaTag(['property' => 'twitter:site', 'content' => Yii::$app->name]);
        $view->registerMetaTag(['property' => 'twitter:type', 'content' => $this->contentType]);
        if (!empty($this->description)) {
            $view->registerMetaTag(['property' => 'twitter:description', 'content' => $this->description]);
        }
        /*
        if ($this->images !== null) {
            $view->registerMetaTag([
                'name' => 'twitter:image', 'content' => (
                is_array($this->images) ?
                    reset($this->images) : $this->images
                )
            ], 'twitter:image');
        }
        */
    }

    public function setContent(ContentActiveRecord $post)
    {
        $this->lastUpdateAt = $post->content->updated_at;
    }


    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return File[]
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * @param File[] $images
     */
    public function setImages(array $images): void
    {
        $this->images = $images;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }


}
