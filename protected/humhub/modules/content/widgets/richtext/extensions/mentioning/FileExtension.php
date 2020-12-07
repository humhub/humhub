<?php

namespace humhub\modules\content\widgets\richtext\extensions\mentioning;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use humhub\modules\content\widgets\richtext\extensions\RichTextExtensionMatch;
use humhub\modules\file\models\File;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

/**
 * This LinkExtension is used to represent mentionings in the richtext as:
 *
 * [<name>](mention:<guid> "<url>")
 *
 */
class FileExtension extends RichTextLinkExtension
{
    public $key = 'file-guid';

    /**
     * @param RichTextExtensionMatch $match
     * @return string
     */
    public function toPlainText(array $block) : string
    {
        $guid = $this->cutExtensionKeyFromUrl($block[static::BLOCK_KEY_URL]);

        $file = File::findOne(['guid' => $guid]);

        if(!$file) {
            return $block[static::BLOCK_KEY_TEXT];
        }

        return static::convertToPlainText($block[static::BLOCK_KEY_TEXT],$file->getUrl());
    }

    public static function buildFileLink(File $file) : string
    {

        return static::buildLink($file->file_name, 'file-guid:'.$file->guid, $file->getUrl([], true));
    }

    public static function buildFileNotFound($name, $guid) : string
    {
        return '['.$name.'](mention:'.$guid.' "#")';
    }

}
