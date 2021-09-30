<?php

namespace humhub\modules\content\widgets\richtext\extensions\link;

use humhub\components\ActiveRecord;
use humhub\modules\content\widgets\richtext\extensions\RichTextContentExtension;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtensionMatch;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;

class RichTextLinkExtension extends RichTextContentExtension
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
     * @param string|null $text rich text content to parse
     * @param string|null $extension extension string if not given all extension types will be included
     * @param callable $callback callable expecting RichTextExtensionMatch as first parameter
     * @return mixed
     */
    public static function replaceLinkExtension(?string $text, ?string $extension, callable $callback)
    {
        return (new static(['key' => $extension]))->replaceExtension($text, $callback);
    }

    /**
     * @param RichTextExtensionMatch $match
     * @return string
     */
    public function initMatch(array $match) : RichTextExtensionMatch
    {
        return new RichTextLinkExtensionMatch(['match' => $match]);
    }


    public static function convertToPlainText($text, $url)
    {
        if(!static::validateNonExtensionUrl($url)) {
            return $text;
        }

        return trim($text).'('.$url.')';
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

    public static function buildExtensionLink(string $text, string $extensionId, string $title = null, string $addition = '') : string
    {
        if(!empty($addition)) {
            $addition = ' '.$addition;
        }

        if(!$title) {
            return '['.$text.']('.static::instance()->key.':'.$extensionId.$addition.')';
        }

        return '['.$text.']('.static::instance()->key.':'.$extensionId.' "'.$title.'"'.$addition.')';
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

        // [<text>](<extension>:<id> "<title>" <addition>   )
        return '/(?<!\\\\)!?\[([^\]]*)\]\(('.$extension.'):{1}([^\)\s]*)(?:\s)?(?:"([^"]*)")?(?:\s)?([^\)]*)\)/is';
    }

    public function onBeforeConvert(string $text, string $format, array $options = []): string
    {
        return $text;
    }

    public function onBeforeConvertLink(LinkParserBlock $linkBlock): void
    {
        // TODO: Implement onBeforeConvertLink() method.
    }

    public function onBeforeOutput(ProsemirrorRichText $richtext, string $output): string {
        return $output;
    }

    public function onAfterOutput(ProsemirrorRichText $richtext, string $output): string  {
        return $output;
    }
}
