<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2022 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\ui\view\components;

use humhub\libs\LogoImage;
use humhub\libs\StringHelper;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\file\converter\PreviewImage;
use humhub\modules\file\models\File;
use Yii;
use yii\base\BaseObject;
use yii\helpers\Url;
use yii\web\Request;

/**
 * Metadata Service for the View class.
 *
 * Supports:
 *  - standard meta tags
 *  - OpenGraph tags
 *  - Twitter Card tags
 *
 * Example usage in controller:
 * ```
 * $this->view->setPageTitle(Yii::t('PostModule.base', 'Post'), true);
 * $this->view->meta->setContent($post);
 * $this->view->meta->setDescription(RichTextToPlainTextConverter::process($post->message));
 * $this->view->meta->setImages($post->fileManager->findAll());
 * ```
 *
 * @since 1.13
 */
class ViewMeta extends BaseObject
{
    /**
     * @var View
     */
    public $view;

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
     * The Preview Image Urls
     * @var string[]
     */
    private $images = [];

    /**
     * @var string
     */
    private $title;

    /**
     * @var string
     */
    private $description;

    /**
     * Called by View to register Tags before rendering
     */
    public function registerMetaTags()
    {
        $this->setDefaults();
        $this->addMetaTags();
        $this->registerOpenGraphMetaTags();
        $this->registerTwitterMetaTags();
    }

    /**
     * @param ContentActiveRecord $content
     */
    public function setContent(ContentActiveRecord $content)
    {
        $this->content = $content;
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
     * @return string[]
     */
    public function getImages(): array
    {
        return $this->images;
    }

    /**
     * Sets an array of File or Image URLs
     *
     * @param File[]|string $images
     */
    public function setImages(array $images): void
    {
        $previewImage = new PreviewImage();
        foreach ($images as $image) {
            if ($previewImage->applyFile($image)) {
                $this->images[] = $image->getUrl();
            }
        }
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

    /**
     * Automatically sets default values
     */
    private function setDefaults()
    {
        if (empty($this->title)) {
            $this->title = $this->view->getPageTitle();
        }

        if (empty($this->contentType)) {
            $this->contentType = 'website';
        }

        if (empty($this->url) && Yii::$app->getRequest() instanceof Request) {
            $this->url = Yii::$app->getRequest()->getAbsoluteUrl();
        }

        if (empty($this->images)) {
            // Use ContentContainer Image
            $contentContainer = ($this->content !== null) ? $this->content->content->container : null;
            if ($contentContainer === null && isset($this->view->context->contentContainer)) {
                $contentContainer = $this->view->context->contentContainer;
            }

            if ($contentContainer !== null && $contentContainer->getProfileImage()->hasImage()) {
                $this->images[] = Url::to($contentContainer->getProfileImage()->getUrl(), true);
            }

            // Fallback to Site Logo
            if (LogoImage::hasImage()) {
                $this->images[] = Url::to(LogoImage::getUrl(600, 600), true);
            }
        }
    }

    /**
     * Add metadata to view to preview the content with Open Graph protocol
     */
    private function addMetaTags()
    {
        $this->view->registerLinkTag(['rel' => 'canonical', 'href' => $this->url]);

        if (!empty($this->description)) {
            $this->view->registerMetaTag([
                'name' => 'description',
                'content' => str_replace("\n", '', StringHelper::truncate($this->description, 255)),
            ]);
        }
    }

    /**
     * Register metadata to preview the content on Facebook and other compatible websites
     *
     * @return void
     */
    private function registerOpenGraphMetaTags()
    {
        $this->view->registerMetaTag(['property' => 'og:url', 'content' => $this->url]);
        $this->view->registerMetaTag(['property' => 'og:site_name', 'content' => Yii::$app->name]);
        $this->view->registerMetaTag(['property' => 'og:type', 'content' => $this->contentType]);

        if (!empty($this->title)) {
            $this->view->registerMetaTag(['property' => 'og:title', 'content' => StringHelper::truncate($this->title, 70)]);
        }
        if (!empty($this->description)) {
            $this->view->registerMetaTag(['property' => 'og:description', 'content' => StringHelper::truncate($this->description, 200)]);
        }

        if (count($this->images) > 0) {
            $this->view->registerMetaTag(['name' => 'og:image', 'content' => $this->images[0]]);
        }
    }

    /**
     * Register metadata to preview the content on Twitter
     *
     * @return void
     */
    private function registerTwitterMetaTags()
    {
        $this->view->registerMetaTag(['property' => 'twitter:url', 'content' => $this->url]);
        $this->view->registerMetaTag(['property' => 'twitter:site', 'content' => Yii::$app->name]);
        $this->view->registerMetaTag(['property' => 'twitter:type', 'content' => $this->contentType]);

        if (!empty($this->title)) {
            $this->view->registerMetaTag(['property' => 'twitter:title', 'content' => StringHelper::truncate($this->title, 70)]);
        }
        if (!empty($this->description)) {
            $this->view->registerMetaTag([
                'property' => 'twitter:description',
                'content' => StringHelper::truncate($this->description, 200)]);
        }
        if (count($this->images) > 0) {
            $this->view->registerMetaTag(['name' => 'twitter:image', 'content' => $this->images[0]]);
        }
    }
}
