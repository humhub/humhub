<?php

namespace humhub\modules\content\widgets\richtext\extensions\link;

use humhub\modules\content\widgets\richtext\extensions\RichTextExtension;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtensionMatch;

class RichTextLinkExtension extends RichTextExtension
{
    const BLOCK_KEY_URL = 'url';
    const BLOCK_KEY_TITLE = 'title';
    const BLOCK_KEY_MD = 'orig';
    const BLOCK_KEY_TEXT = 'text';

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
    public function toPlainText(array $block) : string
    {
        return static::convertToPlainText($block[static::BLOCK_KEY_TEXT], $block[static::BLOCK_KEY_URL]);
    }

    public static function convertToPlainText($text, $url)
    {
        if(!static::validateNonExtensionUrl($url)) {
            return strip_tags($text);
        }

        return trim(strip_tags($text)).'('.$url.')';
    }

    public static function validateNonExtensionUrl($url)
    {
        $protocols = ['http', 'https', 'mailto', '#', 'ftp', 'ftps', '/'];
        foreach ($protocols as $protocol) {
            if(strpos($url, $protocol . ':') === 0) {
                return true;
            }
        }

        return false;
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

    public function validateExtensionUrl(string $url) : bool
    {
        return strpos($url, $this->key . ':') === 0;
    }

    public static function buildLink(string $text, string $url, string $title = null) : string
    {
        if(!$title) {
            return '['.$text.']('.$url.')';
        }

        return '['.$text.']('.$url.' "'.$title.'")';
    }

    public function cutExtensionKeyFromUrl(string $url) : string
    {
        if(!$this->validateExtensionUrl($url)) {
            return $url;
        }

        return substr($url, strlen($this->key . ':'));
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
