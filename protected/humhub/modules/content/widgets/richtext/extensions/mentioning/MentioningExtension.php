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
class MentioningExtension extends RichTextLinkExtension
{
    public $key = 'mention';

    /**
     * @param RichTextExtensionMatch $match
     * @return string
     */
    public function toPlainText(array $block) : string
    {
        $guid = $this->cutExtensionKeyFromUrl($block[static::BLOCK_KEY_URL]);

        $container = $this->findContainer($guid);

        if(!$container || ($container instanceof User && !$container->isActive())) {
            return $block[static::BLOCK_KEY_TEXT];
        }

        return static::convertToPlainText($container->getDisplayName(), $container->createUrl(null, [], true));
    }

    public static function buildMentioning(ContentContainerActiveRecord $container) : string
    {
        if($container instanceof User && !$container->isActive()) {
            return static::buildMentioningNotFound($container->getDisplayName(), $container->guid);
        }

        return static::buildLink($container->getDisplayName(), 'mention:'.$container->guid, $container->getUrl());
    }

    public static function buildMentioningNotFound($name, $guid) : string
    {
        return '['.$name.'](mention:'.$guid.' "#")';
    }

    private function findContainer($guid) : ?ContentContainerActiveRecord
    {
        $result = User::findOne(['guid' => $guid]);

        if(!$result) {
            $result = Space::findOne(['guid' => $guid]);
        }

        return $result;
    }

}
