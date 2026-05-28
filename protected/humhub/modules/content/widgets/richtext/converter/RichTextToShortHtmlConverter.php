<?php

namespace humhub\modules\content\widgets\richtext\converter;

use humhub\helpers\Html;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;

/**
 * Converts richtext content to a short, HTML encoded preview suitable for rendering
 * inside HTML views (e.g. content previews, notification HTML).
 *
 * Output is always HTML encoded. With [[OPTION_NL2BR]] preserved newlines are
 * converted to `<br>` tags.
 *
 * Use [[RichTextToShortTextConverter]] when an unencoded plain text result is needed
 * (e.g. for mail subjects or other non-HTML contexts).
 *
 * @since 1.19
 */
class RichTextToShortHtmlConverter extends RichTextToShortTextConverter
{
    /**
     * Option can be used in combination with [[OPTION_PRESERVE_SPACES]] in order to
     * convert preserved newlines into `<br>` tags.
     */
    public const OPTION_NL2BR = 'nl2br';

    /**
     * @inheritdoc
     */
    public $format = ProsemirrorRichText::FORMAT_SHORT_HTML;

    /**
     * @var array
     */
    public static $cache = [];

    /**
     * @inheritDoc
     */
    protected function onAfterParse($text): string
    {
        $result = parent::onAfterParse($text);
        $result = Html::encode($result);

        if ($this->getOption(static::OPTION_NL2BR, false)) {
            $result = nl2br($result, false);
        }

        return $result;
    }
}
