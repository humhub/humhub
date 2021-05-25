<?php


namespace humhub\modules\content\widgets\richtext;


use humhub\modules\content\widgets\richtext\converter\RichTextToHtmlConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToMarkdownConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToPlainTextConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToShortTextConverter;

/**
 * Converter implementation for richtext ProsemirrorRichText.
 *
 * @package humhub\modules\content\widgets\richtext
 * @since 1.8
 */
class ProsemirrorRichTextConverter extends AbstractRichTextConverter
{

    /**
     * @inheritdoc
     */
    public function convertToHtml(string $content, array $options = []): string
    {
        return RichTextToHtmlConverter::process($content, $options);
    }

    /**
     * @inheritdoc
     */
    public function convertToMarkdown(string $content, array $options = []): string
    {
        return RichTextToMarkdownConverter::process($content, $options);
    }

    /**
     * @inheritdoc
     */
    public function convertToPlaintext(string $content, array $options = []): string
    {
        return RichTextToPlainTextConverter::process($content, $options);
    }

    /**
     * @inheritdoc
     */
    public function convertToShortText(string $content, array $options = []): string
    {
        return RichTextToShortTextConverter::process($content, $options);
    }
}
