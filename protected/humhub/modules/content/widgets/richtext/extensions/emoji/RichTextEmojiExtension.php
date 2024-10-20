<?php

namespace humhub\modules\content\widgets\richtext\extensions\emoji;

use humhub\libs\EmojiMap;
use humhub\modules\content\widgets\richtext\extensions\RichTextContentExtension;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtensionMatch;

/**
 * The emoji richtext extension is responsible for replacing richtext emoji syntax like :smile: to utf8 characters when
 * converting a richtext to other formats.
 */
class RichTextEmojiExtension extends RichTextContentExtension
{
    public const REGEX = '/[:|;]([\p{Latin}\d\-\+][\p{Latin}\d_\-+\s_’“”!\.,#\*()&]*)[:|;]/iu';

    /**
     * @inheritdoc
     */
    public function onBeforeConvert(string $text, string $format, array $options = []): string
    {
        return static::convertEmojiToUtf8($text);
    }

    /**
     * @param $text
     * @return string
     */
    public static function convertEmojiToUtf8($text): string
    {
        // Note the ; was used in the legacy editor
        return static::replace($text, function (RichTextEmojiExtensionMatch $match) {
            return EmojiMap::getUnicode($match->getEmojiName()) ?? $match->getFull();
        });
    }

    /**
     * @inheritdoc
     */
    public function initMatch(array $match): RichTextExtensionMatch
    {
        return new RichTextEmojiExtensionMatch(['match' => $match]);
    }

    /**
     * @inheritdoc
     */
    public function getRegex(): string
    {
        return static::REGEX;
    }
}
