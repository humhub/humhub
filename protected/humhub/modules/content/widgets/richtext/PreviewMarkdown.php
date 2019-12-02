<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets\richtext;


use humhub\libs\Markdown;

class PreviewMarkdown extends Markdown
{
    protected function parseEntity($text)
    {
        // html entities e.g. &copy; &#169; &#x00A9;
        if (preg_match('/^&#?[\w\d]+;/', $text, $matches)) {
            return [['inlineHtml', $matches[0]], strlen($matches[0])];
        } else {
            return [['text', '&'], 1];
        }
    }
}