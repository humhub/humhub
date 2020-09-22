<?php

namespace humhub\modules\ui\widgets;

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
     * @var boolean create link to the space or user profile
     */
    public $link = false;

    /**
     * @var array Html Options of the link
     */
    public $linkOptions = [];

    /**
     * @var string show tooltip with further information about the space or the user (Only available when link is true)
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
}