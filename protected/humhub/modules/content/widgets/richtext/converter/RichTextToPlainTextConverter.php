<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2020 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets\richtext\converter;


use cebe\markdown\GithubMarkdown;
use cebe\markdown\inline\LinkTrait;
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
    protected const IMAGE_SUFFIX = '';
    protected const BOLD_WRAPPER = '';
    protected const EMPHASIZE_WRAPPER = '';
    protected const STRIKE_WRAPPER = '';
    protected const INLINE_CODE_WRAPPER = '';

    /**
     * @inheritdoc
     */
    public $format = ProsemirrorRichText::FORMAT_PLAINTEXT;

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
}
