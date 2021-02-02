<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets\richtext;


use humhub\components\ActiveRecord;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use yii\base\BaseObject;

/**
 * AbstractRichTextConverter classes are used to translate the richtext content to other formats. This class is responsible
 * for converting the base richtext format to other formats. Implementations of this interface at least need to implement
 * the `convertToPlaintext()` function. If a format other than plaintext is not supported the implementation should fallback
 * to an encoded version of `convertToPlaintext()`.
 *
 * @author Julian Harrer <julian.harrer@humhub.com>
 * @since 1.8
 */
abstract class AbstractRichTextConverter extends BaseObject
{
    /**
     * Converts the given rich-text content to HTML.
     *
     * If `$minimal = true` (default) the HTML result should only support a minimal set HTML text features
     * and avoid embedding complex elements as oembeds or iframes. Minimal output may be used in mails and previews.
     *
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
    abstract public function convertToHtml(string $content, array $options = []): string;

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
    abstract public function convertToMarkdown(string $content, array $options = []): string;

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
    abstract public function convertToPlaintext(string $content, array $options = []): string;

    /**
     * Converts the given rich-text content to html encoded short text preview. The short text should not contain any
     * html elements.
     *
     * A proper implementation of this function is mandatory.
     *
     * The $options array may be used to manipulate the result. This converter should support a `maxLength` option
     * in order to cut the result. This is used for example in previews of a rich text.
     *
     * @param string $content
     * @param array $options
     * @return mixed
     */
    abstract public function convertToShortText(string $content, array $options = []): string;
}
