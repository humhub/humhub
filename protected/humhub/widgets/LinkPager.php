<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * HLinkPager 
 *
 * @since 0.12.0
 * @author luke
 */
class HLinkPager extends CLinkPager
{

    public $maxButtonCount = 5;
    public $nextPageLabel = '<i class="fa fa-step-forward"></i>';
    public $prevPageLabel = '<i class="fa fa-step-backward"></i>';
    public $firstPageLabel = '<i class="fa fa-fast-backward"></i>';
    public $lastPageLabel = '<i class="fa fa-fast-forward"></i>';
    public $header = '';
    public $htmlOptions = array('class' => 'pagination');

}
