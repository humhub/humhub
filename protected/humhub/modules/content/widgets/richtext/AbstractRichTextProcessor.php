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
 * Rich text processors are used to post-process the rich text after saving the related content record.
 *
 * By default this includes the parsing of mentionings and oembed links, a rich text implementation may provide further
 * post-processing steps.
 *
 * @author Julian Harrer <julian.harrer@humhub.com>
 * @since 1.3
 */
abstract class AbstractRichTextProcessor extends BaseObject
{
    /**
     * @var string RichText content
     */
    public $text;

    /**
     * @var ContentAddonActiveRecord|ContentActiveRecord $record
     */
    public $record;

    /**
     * @var string the richtext attribute of $record
     */
    public $attribute;

    /**
     * Executes post process logic
     */
    public function process() {
        $result = [];
        $this->parseOembed();

        if($this->record instanceof ContentActiveRecord || $this->record instanceof ContentAddonActiveRecord) {
            $result['mentioning'] = $this->parseMentioning();
        }

        $result['files'] = $this->parseFiles();

        $result['text'] = $this->text;

        return $result;
    }

    /**
     * This function can be used to parse file-guid based links e.g. for auto attachment.
     * @since v1.3.3
     */
    public function parseFiles() {}

    /**
     * This function is called while processing the Richtext content and will parse the given text for urls and preloads the oembed result.
     * Richtext subclasses have to provide their own parsing logic.
     *
     * @param $text string richtext content
     */
    public abstract function parseOembed();

    /**
     * This function is called while processing the richtext content and is responsible for parsing and creating mentionings.
     * Richtext implementations have to provide their own parsing logic.
     *
     *  e.g:
     *
     * ```php
     * $guids = someParserLogic();
     * return Mentioning::mention($guids, $record);
     * ```
     * @param $message
     * @return array list of successfully mentioned users
     */
    public abstract function parseMentioning();
}
