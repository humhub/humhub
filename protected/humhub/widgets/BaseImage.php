<?php

namespace humhub\widgets;

use humhub\components\Widget;

class BaseImage extends Widget
{
    /**
     * @var int the width of the image
     */
    public $width = 50;

    /**
     * @var int the height of the image
     */
    public $height = null;

    /**
     * @var array html options for the generated tag
     */
    public $htmlOptions = [];

    /**
     * @var bool create link to the space or user profile
     */
    public $link = false;

    /**
     * @var array Html Options of the link
     */
    public $linkOptions = [];

    /**
     * @var bool show tooltip with further information about the space or the user (Only available when link is true)
     * @since 1.3
     */
    public $showTooltip = false;

    /**
     * @var string the tooltip text (default is users or spaces display name)
     * @since 1.3
     */
    public $tooltipText = null;

    /**
     * @var array optional html options for the image tag
     */
    public $imageOptions = [];

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->height === null) {
            $this->height = $this->width;
        }
    }

    /**
     * The inline size of the image. The CSS variables `--hh-content-container-image-width` and `--hh-content-container-image-height` override it, so a
     * stylesheet can change the size without `!important` (e.g. the profile header on a phone).
     *
     * @param int|string $width in px
     * @param int|string $height in px
     * @return string
     * @since 1.20
     */
    public static function getSizeStyle($width, $height): string
    {
        return 'width: var(--hh-content-container-image-width, ' . $width . 'px); height: var(--hh-content-container-image-height, ' . $height . 'px);';
    }
}
