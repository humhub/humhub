<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 *
 */

namespace humhub\modules\content\widgets\richtext;


use humhub\models\UrlOembed;
use humhub\modules\user\models\Mentioning;

/**
 * Legacy rich text processing logic.
 *
 * @deprecated since 1.3 this hold the post-processing logic for the legacy humhub rich text
 */
class HumHubRichTextProcessor extends AbstractRichTextProcessor
{

    /**
     * @inheritdoc
     */
    public function parseOembed()
    {
        preg_replace_callback('/http(.*?)(\s|$)/i', function ($match) {
            UrlOembed::preload($match[0]);
        }, $this->text);
    }

    /**
     * @inheritdoc
     */
    public function parseMentioning()
    {
        $result = [];

        preg_replace_callback('@\@\-u([\w\-]*?)($|\s|\.)@', function ($hit) use (&$record, &$result) {
            $result = array_merge($result, Mentioning::mention($hit[1], $this->record));
        }, $this->text);

        return $result;
    }
}
