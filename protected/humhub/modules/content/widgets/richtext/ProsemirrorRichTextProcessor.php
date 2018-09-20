<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets\richtext;


use humhub\models\UrlOembed;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\user\models\Mentioning;

/**
 * Class ProsemirrorRichTextProcessor provides pre-processor logic for oembed and mentionings for the ProsemirrorRichText.
 *
 * @author Julian Harrer <julian.harrer@humhub.com>
 * @since 1.3
 */
class ProsemirrorRichTextProcessor extends AbstractRichTextProcessor
{

    /**
     * Parses oembed link extensions in the form of [<url>](oembed:<url>) and preloads the given oembed dom.
     */
    public function parseOembed()
    {
        $matches = ProsemirrorRichText::scanLinkExtension($this->text, 'oembed');
        foreach ($matches as $match) {
            if(isset($match[3])) {
                UrlOembed::preload($match[3]);
            }
        }
    }

    /**
     * Parses mention link extensions in the form of [<url>](mention:<guid> "<link>") and creates mentionings records.
     */
    public function parseMentioning()
    {
        $result = [];
        $matches = ProsemirrorRichText::scanLinkExtension($this->text, 'mention');
        foreach ($matches as $match) {
            if(isset($match[3])) {
                $result = array_merge($result, Mentioning::mention($match[3], $this->record));
            }
        }

        return $result;
    }

    public function parseFiles()
    {
        $result = [];
        $matches = ProsemirrorRichText::scanLinkExtension($this->text, 'file-guid');
        foreach ($matches as $match) {
            if(isset($match[3])) {
                try {
                    $this->record->fileManager->attach($match[3]);
                } catch (\Exception $e) {
                    Yii::error($e);
                }
            }
        }

        return $result;
    }
}
