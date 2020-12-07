<?php


namespace humhub\modules\content\widgets\richtext;


use humhub\libs\EmojiMap;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtensionMatch;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use humhub\modules\content\widgets\richtext\parsers\RichTextToPlainTextConverter;

class ProsemirrorRichTextConverter extends AbstractRichTextConverter
{

    /**
     * Converts the given rich-text content to HTML.
     *
     * If `$minimal = true` (default) the HTML result should only support a minimal set HTML text features
     * and avoid embedding complex elements as oembeds or iframes. Minimal output may be used in mails and previews.
     *
     * If `$minimal = false` the HTML result should support and translate as many richtext features as possible.
     *
     * If not supported, this function should at least return a HTML encoded version of `convertToPlaintext()`
     *
     * The $options array may be used to manipulate the result e.g. by exluding/including richtext features.
     * The supported options may differ between richtext implementations.
     *
     * @param $content richtext content
     * @param bool $minimal if true generates only a simple html output (default) otherwise includes as many richtext features as possible
     * @param array $options
     * @return string
     */
    public function convertToHtml(string $content, bool $minimal = true, array $options = []): string
    {
        return '';
    }

    /**
     * Converts the given rich-text content to plain markdown.
     *
     * If richtext format is already based on markdown, this function is merely responsible for removing richtext specific
     * markdown extensions as oembeds, mentionings, emojis.
     *
     * If not supported, this function should at least return a HTML encoded version of `convertToPlaintext()`
     *
     * The $options array may be used to manipulate the result e.g. by exluding/including richtext features.
     * The supported options may differ between richtext implementations.
     *
     * @param string $content
     * @param array $options
     * @return mixed
     */
    public function convertToMarkdown(string $content, array $options = []): string
    {
        return '';
    }

    /**
     * Converts the given rich-text content to non html encoded plain text.
     *
     * A proper implementation of this function is mandatory.
     *
     * The $options array may be used to manipulate the result.
     * The supported options may differ between richtext implementations.
     *
     * @param string $content
     * @param array $options
     * @return mixed
     */
    public function convertToPlaintext(string $content, array $options = []): string
    {
        $result = static::convertEmojiToUtf8($content);
       // $result = static::purifyLinks($result);
        return (new RichTextToPlainTextConverter)->parse($result);
    }

    /**
     * Purifies links from extensions as image size extensions, otherwise
     * @param $text
     * @return mixed
     */
    public static function purifyLinks($text)
    {
        return RichTextLinkExtension::replaceLinkExtension($text, null, function(RichTextExtensionMatch $match) {
            return RichTextLinkExtension::buildLink($match->getText(), $match->getUrl(), $match->getTitle());
        });
    }

    public static function convertEmojiToUtf8($text)
    {
        // Note the ; was used in the legacy editor
        return preg_replace_callback('/[:|;](([A-Za-z0-9])+)[:|;]/', function($match)  {
            $result =  $match[0];

            if(isset($match[1])) {
                $result = array_key_exists(strtolower($match[1]), EmojiMap::MAP) ?  EmojiMap::MAP[strtolower($match[1])] : $result;
            }

            return $result;
        }, $text);
    }
}
