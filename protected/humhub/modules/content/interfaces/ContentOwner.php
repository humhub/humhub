<?php

namespace humhub\modules\content\interfaces;

use humhub\modules\content\models\Content;

/**
 * Interface for classes which are able to return content instances.
 *
 * @see Content
 * @author buddha
 * @since 1.2
 */
interface ContentOwner
{
    /**
     * @returns Content content instance of this content owner
     */
    public function getContent();

    /**
     * @returns string name of the content like 'comment', 'post'
     */
    public function getContentName();

    /**
     * Returns a text, markdown or richtext description e.g. the message of a post which is for example
     * used for content previews and mails.
     *
     * @returns string a plaintext, markdown or richtext description e.g. the message of a post
     */
    public function getContentDescription();
}
