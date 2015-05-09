<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */
class SearchModule extends HWebModule
{

    public $isCoreModule = true;

    public function init()
    {
        $this->setImport(array(
            'post.models.*',
            'post.behaviors.*',
        ));
    }

    public function rebuild()
    {
        
    }

}
