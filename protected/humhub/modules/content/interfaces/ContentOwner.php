<?php
namespace humhub\modules\content\interfaces;

/**
 *
 * @author luke
 */
interface ContentOwner
{
    public function getContent();
    
    public function getContentName();

    public function getContentDescription();
}
