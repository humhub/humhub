<?php


namespace humhub\modules\content\widgets\richtext\parsers;


use cebe\markdown\GithubMarkdown;
use cebe\markdown\inline\LinkTrait;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use yii\helpers\Url;

/**
 * Converts from plain markdown content to plain text.
 *
 * > Note: the result of this parser will not be encoded, so do not directly append the result to a html document without escaping.
 * > Note: this class should not be used for richtext enabled markdown.
 *
 * @since 1.8
 */
class PlaintextMarkdownParser extends GithubMarkdown
{

    use LinkTrait {
        parseLt as linkParseLt;
    }

    protected function renderLink($block)
    {
        return RichTextLinkExtension::convertToPlainText($this->renderAbsy($block['text']), $this->getAbsoluteUrl($block['url']));
    }

    protected function parseEntity($text)
    {
        // html entities e.g. &copy; &#169; &#x00A9;
        if (preg_match('/^&#?[\w\d]+;/', $text, $matches)) {
            return [['inlineHtml', $matches[0]], strlen($matches[0])];
        } else {
            return [['text', '&'], 1];
        }
    }

    protected function renderParagraph($block)
    {
        return $this->renderAbsy($block['content'])."\n\n";
    }

    protected function parseLt($text)
    {
        if (strpos($text, '>') !== false) {
            return $this->linkParseLt($text);
        }
;
        return [['text', '<'], 1]; // Do not
    }

    /**
     * Escapes `>` characters.
     * @marker >
     */
    protected function parseGt($text)
    {
        return [['text', '>'], 1];
    }

    protected function renderImage($block)
    {
        return RichTextLinkExtension::convertToPlainText($block['text'], $this->getAbsoluteUrl($block['url']));
    }

    private function getAbsoluteUrl($url)
    {
        if($url && $url[0] === '/') {
            return Url::base(true).$url;
        }

        return $url;
    }

    public function parse($text)
    {
        return strip_tags(parent::parse($text));
    }
}
