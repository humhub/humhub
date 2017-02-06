<?php
namespace humhub\modules\content\interfaces;

/**
 * Interface for classes able to return cotnent instances.
 * @author buddha
 * @since 1.2
 */
interface ContentOwner
{
    /**
     * @returns \humhub\modules\content\models\Content content instance of this content owner
     */
    public function getContent();
    
    /**
     * @returns string name of the content like 'comment', 'post'
     */
    public function getContentName();

    /**
     * @returns string short content description
     */
    public function getContentDescription();
}
