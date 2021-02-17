<?php

namespace humhub\modules\content\widgets\richtext\extensions\mentioning;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\content\components\ContentAddonActiveRecord;
use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\content\widgets\richtext\extensions\link\LinkParserBlock;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtension;
use humhub\modules\content\widgets\richtext\extensions\link\RichTextLinkExtensionMatch;
use humhub\modules\content\widgets\richtext\ProsemirrorRichText;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\Mentioning;
use humhub\modules\user\models\User;
use humhub\components\ActiveRecord;

/**
 * This LinkExtension is used to represent mentionings in the richtext as:
 *
 * [<name>](mention:<guid> "<url>")
 *
 */
class MentioningExtension extends RichTextLinkExtension
{
    public $key = 'mention';

    public function onBeforeOutput(ProsemirrorRichText $richtext, string $output) : string
    {
        return static::replace($output, function(RichTextLinkExtensionMatch $match) use ($richtext) {
            $contentContainer = ContentContainer::findOne(['guid' => $match->getExtensionId()]);

            if(!$contentContainer || !$contentContainer->getPolymorphicRelation()) {
                // If no user or space was found we leave out the url in the non edit mode.
                return $richtext->edit
                    ? static::buildExtensionLink($match->getText(), $match->getText(), $match->getTitle())
                    : static::buildMentioningNotFound($match->getText(), $match->getExtensionId());
            }

            $container = $contentContainer->getPolymorphicRelation();

            if($container instanceof User) {
                return $container->isActive()
                    ? static::buildMentioning($container)
                    : static::buildMentioningNotFound($match->getText(), $match->getExtensionId());
            }

            if($container instanceof Space) {
                return self::buildMentioning($container);
            }

            return '';
        });
    }

    /**
     * @inheritDoc
     */
    public function onBeforeConvertLink(LinkParserBlock $linkBlock) : void
    {
        $guid = $this->cutExtensionKeyFromUrl($linkBlock->getUrl());

        $container = $this->findContainer($guid);

        if(!$container || ($container instanceof User && !$container->isActive())) {
            $linkBlock->setResult('@'.$linkBlock->getParsedText());
            return;
        }

        $linkBlock->setBlock('@'.$container->getDisplayName(), $container->createUrl(null, [], true));
    }

    private function findContainer($guid) : ?ContentContainerActiveRecord
    {
        $result = User::findOne(['guid' => $guid]);

        if(!$result) {
            $result = Space::findOne(['guid' => $guid]);
        }

        return $result;
    }

    public static function buildMentioning(ContentContainerActiveRecord $container, $urlScheme = false) : string
    {
        if($container instanceof User && !$container->isActive()) {
            return static::buildMentioningNotFound($container->getDisplayName(), $container->guid);
        }

        return static::buildLink($container->getDisplayName(), 'mention:'.$container->guid, $container->getUrl($urlScheme));
    }

    private static function buildMentioningNotFound($name, $guid) : string
    {
        return static::buildExtensionLink($name, $guid);
    }

    public function onPostProcess(string $text, ActiveRecord $record, ?string $attribute, array &$result): string
    {
        $result[$this->key] = [];

        // We currently only support mentionings in content or content addon records
        if(!($record instanceof ContentActiveRecord) && !($record instanceof ContentAddonActiveRecord)) {
            return $text;
        }

        foreach ($this->scanExtension($text) as $match) {
            if($match->getExtensionId()) {
                $mention = Mentioning::mention($match->getExtensionId(), $record);
                if(!empty($mention)) {
                    $result[$this->key][] = $mention[0];
                }
            }
        }

        // Compatibility with HumHub < 1.8
        $result['mentioning'] = $result[$this->key];

        return $text;
    }
}
