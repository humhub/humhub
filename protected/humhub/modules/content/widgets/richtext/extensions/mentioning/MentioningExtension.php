<?php

namespace humhub\modules\content\widgets\richtext\extensions;

use humhub\modules\content\components\ContentContainerActiveRecord;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;

class MentioningExtension extends RichTextLinkExtension
{
    public $key = 'mention';

    /**
     * @param RichTextExtensionMatch $match
     * @return string
     */
    public function toPlainText(RichTextExtensionMatch $match) : string
    {
        $guid = $match->getExtensionId();

        $container = $this->findContainer($guid);

        if(!$container) {
            return '';
        }

        return static::convertToPlainText($container->getDisplayName(), $match->getUrl());
    }

    private function findContainer($guid) : ContentContainerActiveRecord
    {
        $result = User::findOne(['guid' => $guid]);

        if(!$result) {
            $result = Space::findOne(['guid' => $guid]);
        }

        return $result;
    }

}
