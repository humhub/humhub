<?php

namespace humhub\modules\content\widgets\richtext\extensions\emoji;

use humhub\libs\EmojiMap;
use humhub\modules\content\widgets\richtext\extensions\mentioning\MentioningExtension;
use humhub\modules\content\widgets\richtext\extensions\RichTextContentExtension;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtensionMatch;
use humhub\components\ActiveRecord;

/**
 * The emoji richtext extension is responsible for replacing richtext emoji syntax like :smile: to utf8 characters when
 * converting a richtext to other formats.
 *
 * @package humhub\modules\content\widgets\richtext\extensions\emoji
 */
class RichTextEmojiExtension extends RichTextContentExtension
{

    /**
     * @inheritdoc
     */
    const REGEX = '/[:|;](([A-Za-z0-9_\-+])+)[:|;]/';

    /**
     * @inheritdoc
     */
    public function onBeforeConvert(string $text, string $format, array $options = []) : string {
        return static::convertEmojiToUtf8($text);
    }

    /**
     * @param $text
     * @return string
     */
    public static function convertEmojiToUtf8($text) : string
    {
        // Note the ; was used in the legacy editor
        return static::replace($text, function(RichTextEmojiExtensionMatch $match) {
            if(!empty($match->getEmojiName())) {
                $name = $match->getEmojiName();
                return array_key_exists(strtolower($name), EmojiMap::MAP)
                    ? EmojiMap::MAP[strtolower($name)]
                    : $match->getFull();
            }

            return $match->getFull();
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
