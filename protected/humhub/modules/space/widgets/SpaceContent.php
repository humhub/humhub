<?php

namespace humhub\modules\space\widgets;

use humhub\components\Widget;
use humhub\modules\content\components\ContentContainerActiveRecord;

class SpaceContent extends Widget
{
    /**
     * @var string
     */
    public $content = '';

    /**
     * @var ContentContainerActiveRecord
     */
    public $contentContainer;

    public function run()
    {
        return $this->content;
    }
}
