<?php

namespace humhub\modules\content\widgets\richtext\converter;

use humhub\modules\content\widgets\richtext\extensions\link\LinkParserBlock;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use Yii;

/**
 * Converts richtext content to a short, unencoded plain text preview.
 *
 * The result is **not** HTML encoded and must not be rendered directly in HTML
 * views without further escaping. Suitable for plain text contexts like mail
 * subjects.
 *
 * For an HTML encoded short preview use [[RichTextToShortHtmlConverter]].
 */
class RichTextToShortTextConverter extends RichTextToPlainTextConverter
{
    /**
     * Option can be used to preserve spaces and new lines in the converter result (default false).
     * Note, this option will not affect cached results and therefore does not require a special cache key.
     */
    public const OPTION_PRESERVE_SPACES = 'preserveNewlines';

    /**
     * @inheritdoc
     */
    public $format = ProsemirrorRichText::FORMAT_SHORT_TEXT;

    /**
     * @inheritdoc
     */
    public $identifyTable = true;

    /**
     * @inheritdoc
     */
    public $identifyQuote = true;

    /**
     * @var array
     */
    public static $cache = [];

    /**
     * @inheritDoc
     */
    protected function renderPlainLink(LinkParserBlock $linkBlock): string
    {
        return $linkBlock->getParsedText();
    }

    /**
     * @inheritDoc
     */
    protected function renderQuote($block)
    {
        return $this->renderAbsy($block['content']);
    }

    /**
     * @param $block
     * @return string
     */
    protected function renderHr($line)
    {
        return "";
    }

    /**
     * @inheritDoc
     */
    protected function renderCode($block)
    {
        return Yii::t('ContentModule.richtexteditor', '[Code Block]') . "\n\n";
    }

    /**
     * @inheritDoc
     */
    protected function renderTable($block)
    {
        return Yii::t('ContentModule.richtexteditor', '[Table]') . "\n\n";
    }

    /**
     * @inheritDoc
     */
    protected function renderHeadline($block)
    {
        return $this->renderAbsy($block['content']) . "\n\n";
    }

    /**
     * @param $block
     * @return string
     */
    protected function renderImage($block)
    {
        $pattern = '/\([^)]+?\b(video|audio)\b[^)]*?\)/i';
        $type = preg_match($pattern, $block['orig'], $type) ? ucfirst($type[1]) : 'Image';
        return Yii::t('ContentModule.richtexteditor', '[' . $type . ']') . "\n\n";
    }

    /**
     * @inheritDoc
     */
    protected function renderPlainImage(LinkParserBlock $linkBlock): string
    {
        $url = $linkBlock->getUrl();
        return RichTextLinkExtension::validateNonExtensionUrl($url) ? $url : '';
    }

    /**
     * @inheritDoc
     */
    protected function onAfterParse($text): string
    {
        $result = (string)$text;

        if (!$this->getOption(static::OPTION_PRESERVE_SPACES, false)) {
            $result = trim((string) preg_replace('/\s+/', ' ', $result));
        }

        return parent::onAfterParse($result);
    }
}
