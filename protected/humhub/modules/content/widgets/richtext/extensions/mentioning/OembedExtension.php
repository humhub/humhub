<?php

namespace humhub\modules\content\widgets\richtext\extensions\mentioning;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtensionMatch;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

/**
 * This LinkExtension is used to represent mentionings in the richtext as:
 *
 * [<name>](mention:<guid> "<url>")
 *
 */
class OembedExtension extends RichTextLinkExtension
{
    public $key = 'oembed';

    /**
     * @param RichTextExtensionMatch $match
     * @return string
     */
    public function toPlainText(array $block) : string
    {
        return $this->cutExtensionKeyFromUrl($block[static::BLOCK_KEY_URL]);
    }

    public static function builOembed($url) : string
    {

        return static::buildLink($url, 'oembed:'.$url);
    }

    public static function buildOembedNotFound($url) : string
    {
        return '['.$url.']('.$url.')';
    }
}
