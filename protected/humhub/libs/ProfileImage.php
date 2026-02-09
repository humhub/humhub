<?php

namespace humhub\libs;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\space\models\Space;
use humhub\modules\space\widgets\Image as SpaceImage;
use humhub\modules\user\widgets\Image as UserImage;

/**
 * @deprecated since 1.19
 */
class ProfileImage
{
    protected $guid = '';
    protected $container;

    public function __construct($guid, protected $defaultImage = 'default_user')
    {
        if ($guid instanceof ContentContainerActiveRecord) {
            $this->container = $guid;
            $this->guid = $this->container->guid;
        } else {
            $this->guid = $guid;
        }
    }

    public function getContainer()
    {
        if (!$this->container) {
            $this->container = ContentContainer::findRecord([$this->guid]);
        }

        return $this->container;
    }

    public function getUrl(?string $compat, bool $scheme = false)
    {
        return $this->container->image->getUrl(null, $scheme);
    }

    public function render($width = 32, $cfg = [])
    {
        $container = $this->getContainer();

        if (!$container) {
            return '';
        }

        $cfg['width'] = $width;
        $widgetOptions = ['width' => $width];

        // TODO: improve option handling...
        if (isset($cfg['link'])) {
            $widgetOptions['link'] = $cfg['link'];
            unset($cfg['link']);
        }

        if (isset($cfg['showTooltip'])) {
            $widgetOptions['showTooltip'] = $cfg['showTooltip'];
            unset($cfg['showTooltip']);
        }

        if (isset($cfg['tooltipText'])) {
            $widgetOptions['tooltipText'] = $cfg['tooltipText'];
            unset($cfg['tooltipText']);
        }

        if ($container instanceof Space) {
            $widgetOptions['space'] = $container;
            $widgetOptions['htmlOptions'] = $cfg;
            return SpaceImage::widget($widgetOptions);
        }

        if (isset($cfg['showSelfOnlineStatus'])) {
            $widgetOptions['showSelfOnlineStatus'] = $cfg['showSelfOnlineStatus'];
            unset($cfg['showSelfOnlineStatus']);
        }

        $htmlOptions = [];

        if (isset($cfg['htmlOptions'])) {
            $htmlOptions = $cfg['htmlOptions'];
            unset($cfg['htmlOptions']);
        }

        $widgetOptions['user'] = $container;
        $widgetOptions['imageOptions'] = $cfg;
        $widgetOptions['htmlOptions'] = $htmlOptions;

        return UserImage::widget($widgetOptions);
    }

}
