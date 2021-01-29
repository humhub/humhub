<?php

namespace humhub\modules\content\widgets\richtext\extensions\emoji;

use humhub\libs\EmojiMap;
use humhub\modules\content\widgets\richtext\extensions\RichTextContentExtension;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtensionMatch;
use humhub\components\ActiveRecord;

class RichTextEmojiExtension extends RichTextContentExtension
{

    /**
     * @inheritdoc
     */
    const REGEX = '/[:|;](([A-Za-z0-9])+)[:|;]/';

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
        return preg_replace_callback(static::REGEX, function($match)  {
            $result =  $match[0];

            if(isset($match[1])) {
                $result = array_key_exists(strtolower($match[1]), EmojiMap::MAP) ?  EmojiMap::MAP[strtolower($match[1])] : $result;
            }

            return $result;
        }, $text);
    }

    /**
     * @inheritdoc
     */
    public function initMatch(array $match): RichTextExtensionMatch
    {
        return new RichTextEmojiExtensionMatch($match);
    }

    /**
     * @inheritdoc
     */
    public function getRegex(): string
    {
        return static::REGEX;
    }
}
