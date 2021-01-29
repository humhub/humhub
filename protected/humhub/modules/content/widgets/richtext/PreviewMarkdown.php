<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2019 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\widgets\richtext;

use humhub\libs\Markdown;

/**
 * Class PreviewMarkdown
 * @package humhub\modules\content\widgets\richtext
 * @deprecated since 1.8 use `Richtext::convert()` for richtext or a parser from `humhub\modules\content\widgets\richtext\converter` for
 * plain markdown parsing.
 */
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
