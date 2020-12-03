<?php

namespace humhub\modules\content\widgets\richtext\extensions\emoji;

use humhub\modules\content\widgets\richtext\extensions\RichTextExtension;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtensionMatch;

class RichTextEmojiExtension extends RichTextExtension
{

    const REGEX = '/[:|;](([A-Za-z0-9])+)[:|;]/';

    /**
     * @param RichTextExtensionMatch $match
     * @return string
     */
    public function initMatch(array $match): RichTextExtensionMatch
    {
        return new RichTextEmojiExtensionMatch($match);
    }

    /**
     * @param array $match
     * @return string
     */
    public function getRegex(): string
    {
        return static::REGEX;
    }

    /**
     * @param RichTextExtensionMatch $match
     * @return string
     */
    public function toPlainText(RichTextExtensionMatch $match) : string
    {
        return $match instanceof RichTextEmojiExtensionMatch ? $match->getAsUtf8() : '';
    }
}
