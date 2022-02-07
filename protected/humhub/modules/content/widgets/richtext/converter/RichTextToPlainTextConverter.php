<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets\richtext\converter;


use cebe\markdown\GithubMarkdown;
use cebe\markdown\inline\LinkTrait;
use humhub\libs\Helpers;
use humhub\modules\content\widgets\richtext\extensions\link\LinkParserBlock;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtensionMatch;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtension;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use yii\helpers\Url;

/**
 * This parser can be used to convert richtext or plain markdown to a plain text format used for example in
 * plain text emails.
 *
 * The [[addExtension()]] function can be used to add additional richtext extensions. By default all extensions registered
 * in [[ProsemirrorRichText::getExtensions()]] are available.
 *
 * > Note: The result of this parser will not be encoded, so do not directly add the result to a HTML view  without
 * encoding it.
 *
 * @since 1.8
 */
class RichTextToPlainTextConverter extends RichTextToMarkdownConverter
{
    /**
     * @var int inline text counter used to skip block parsing if maxLength is reached
     */
    private $textCount = 0;

    /**
     * @var bool if true will skip further block parsing, this flag set if textCount reaches maxLength
     */
    private $skipBlocks = false;

    /**
     * @inheritdoc
     */
    protected const IMAGE_SUFFIX = '';

    /**
     * @inheritdoc
     */
    protected const BOLD_WRAPPER = '';

    /**
     * @inheritdoc
     */
    protected const EMPHASIZE_WRAPPER = '';

    /**
     * @inheritdoc
     */
    protected const STRIKE_WRAPPER = '';

    /**
     * @inheritdoc
     */
    protected const INLINE_CODE_WRAPPER = '';

    /**
     * Option can be used to trim a text to a certain length.
     * Note, this option will not affect cached results and therefore does not require a special cache key.
     */
    public const OPTION_MAX_LENGTH = 'maxLength';

    /**
     * @inheritdoc
     */
    public $format = ProsemirrorRichText::FORMAT_PLAINTEXT;

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
    protected function renderPlainLink(LinkParserBlock $linkBlock) : string
    {
        if($linkBlock->getParsedText() === $linkBlock->getUrl()) {
            return $linkBlock->getUrl();
        }

        return RichTextLinkExtension::convertToPlainText($linkBlock->getParsedText(), $linkBlock->getUrl());
    }

    /**
     * @inheritdoc
     */
    protected function parseInline($text)
    {
        $paragraph = parent::parseInline($text);

        $maxLength = $this->getOption(static::OPTION_MAX_LENGTH, 0);

        // In case of a given cache key, we need to make sure to parse and cache the full text
        if(!$maxLength || $this->getOption(static::OPTION_CACHE_KEY, null)) {
            return $paragraph;
        }

        foreach ($paragraph as $inline) {
            if(isset($inline[0], $inline[1]) && $inline[0] === 'text') {
                $this->textCount += mb_strlen($inline[1]);
                if($this->textCount > $maxLength) {
                    $this->skipBlocks = true;
                }
            }
        }

        return $paragraph;
    }

    /**
     * @inheritdoc
     */
    protected function parseBlock($lines, $current)
    {
        if($this->skipBlocks) {
            return [false, count($lines)];
        }

        // TODO: directly exclude blocks from option
        // identify block type for this line
        $blockType = $this->detectLineType($lines, $current);

        // call consume method for the detected block type to consume further lines
        return $this->{'consume' . $blockType}($lines, $current);
    }

    /**
     * @inheritDoc
     */
    protected function renderPlainImage(LinkParserBlock $linkBlock) : string {
        return $this->renderPlainLink($linkBlock);
    }

    /**
     * Returns a plain text representation of an email
     * @param $block
     * @return string
     */
    protected function renderEmail($block)
    {
        return $block[1];
    }

    /**
     * @inheritDoc
     *
     * Allows escaping newlines to create line breaks.
     *
     * Parses for hard breaks as `FirstLine\\\nSecondLine`
     *
     * @see https://github.com/cebe/markdown/issues/169
     * @marker \
     */
    protected function parseEscape($text)
    {
        # If the backslash is followed by a newline.
        # Note: GFM doesn't allow spaces after the backslash.
        if (isset($text[1]) && $text[1] === "\n") {
            # Here we just skip the  escape, since we have a new line anyways
            return [["text", ''], 1];
        }

        # Otherwise parse the sequence normally
        return parent::parseEscape($text);
    }

    /**
     * @inheritDoc
     */
    protected function onAfterParse($text) : string
    {
        $result = parent::onAfterParse($text);
        $maxLength = $this->getOption(static::OPTION_MAX_LENGTH, 0);

        if($maxLength > 0) {
            $result = Helpers::truncateText($result, $maxLength);
        }

        return $result;
    }
}
