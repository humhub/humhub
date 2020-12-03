<?php

namespace humhub\modules\content\widgets\richtext\extensions\link;

use humhub\modules\content\widgets\richtext\extensions\RichTextExtension;
use humhub\modules\content\widgets\richtext\parsers\PlaintextMarkdownParser;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtensionMatch;

class RichTextLinkExtension extends RichTextExtension
{
    /**
     * Can be used to scan for link extensions of the form [<text>](<extension>:<url> "<title>") in which the actual meaning
     * of the placeholders is up to the extension itself.
     *
     * @param $text string rich text content to parse
     * @param $extension string|null extension string if not given all extension types will be included
     * @return RichTextExtensionMatch[]
     */
    public static function scanLinkExtension($text, $extension = null)
    {
        return (new static(['key' => $extension]))->scanExtension($text);
    }

    /**
     * Can be used to scan and replace link extensions of the form [<text>](<extension>:<url> "<title>") in which the actual meaning
     * of the placeholders is up to the extension itself.
     *
     * @param $text string rich text content to parse
     * @param $extension string|null extension string if not given all extension types will be included
     * @param callable $callback callable expecting MarkdownLinkMatch[] as first parameter
     * @return mixed
     */
    public static function replaceLinkExtension(string $text, $extension, callable $callback)
    {
        return (new static(['key' => $extension]))->replaceExtension($text, $callback);
    }

    /**
     * @param RichTextExtensionMatch $match
     * @return string
     */
    public function toPlainText(RichTextExtensionMatch $match) : string
    {
        return static::convertToPlainText($match->getContent(), $match->getUrl());
    }

    public static function convertToPlainText($linkContent, $url)
    {
        $content = (new PlaintextMarkdownParser)->parse($linkContent);
        return trim(strip_tags($content)).'('.$url.')';
    }

    /**
     * @param RichTextExtensionMatch $match
     * @return string
     */
    public function initMatch(array $match) : RichTextExtensionMatch
    {
        return new RichTextLinkExtensionMatch(['match' => $match]);
    }

    /**
     * @param array $match
     * @return string
     */
    public function getRegex(): string
    {
        return static::getLinkExtensionPattern($this->key);
    }

    /**
     * @param string $extension the extension to parse, if not set all extensions are included
     * @return string the regex pattern for a given extension or all extension if no specific extension string is given
     */
    public static function getLinkExtensionPattern($extension = '[a-zA-Z-_]+') : string
    {
        if($extension === null) {
            $extension  = '[a-zA-Z-_]+';
        }

        // [<text>](<extension>:<id> "<title>")
        return '/(?<!\\\\)!?\[([^\]]*)\]\(('.$extension.'):{1}([^\)\s]*)(?:\s)?(?:"([^"]*)")?[^\)]*\)/is';
    }
}
