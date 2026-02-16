<?php

namespace humhub\modules\content\helpers;

use humhub\helpers\Html;
use humhub\modules\content\interfaces\ContentOwner;
use humhub\modules\content\widgets\richtext\converter\RichTextToPlainTextConverter;
use humhub\modules\content\widgets\richtext\converter\RichTextToShortTextConverter;
use Yii;

final class ContentHelper
{
    /**
     * Build info text about a content
     *
     * This is a combination of the type of the content with a short preview
     * of it.
     */
    public static function getContentInfo(ContentOwner $content, $withContentName = true): string
    {
        $info = self::getContentPreview($content, 60);

        if (empty($info)) {
            return '';
        }

        return ($withContentName) ? Html::encode($content->getContentName()) . ' "' . $info . '"' : $info;
    }

    public static function getContentPlainTextInfo(ContentOwner $content, $withContentName = true): string
    {
        $info = self::getContentPlainTextPreview($content);
        return ($withContentName) ? $content->getContentName() . ' "' . $info . '"' : $info;
    }


    /**
     * Returns a short preview text of the content. The max length can be defined by setting
     * $maxLength (60 by default).
     *
     *  If no $content is provided the contentPreview of $source is returned.
     */
    public static function getContentPreview(ContentOwner $content, $maxLength = 60): string
    {
        return RichTextToShortTextConverter::process($content->getContentDescription(), [
            RichTextToShortTextConverter::OPTION_MAX_LENGTH => $maxLength,
            RichTextToShortTextConverter::OPTION_CACHE_KEY => RichTextToShortTextConverter::buildCacheKeyForContent($content),
        ]);
    }

    /**
     * Returns a short preview text of the content in plain text. The max length can be defined by setting
     * $maxLength (60 by default).
     *
     *  If no $content is provided the contentPreview of $source is returned.
     *
     * Note: This should only be used for mail subjects and other plain text
     */
    public static function getContentPlainTextPreview(ContentOwner $content, $maxLength = 60): string
    {
        try {
            return RichTextToPlainTextConverter::process($content->getContentDescription(), [
                RichTextToPlainTextConverter::OPTION_MAX_LENGTH => $maxLength,
                RichTextToPlainTextConverter::OPTION_CACHE_KEY => RichTextToPlainTextConverter::buildCacheKeyForContent($content),
            ]);
        } catch (\Exception $e) {
            Yii::error($e);
        }

        return '';
    }
}
