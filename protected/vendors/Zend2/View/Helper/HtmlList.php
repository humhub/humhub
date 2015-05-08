<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2013 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\View\Helper;

/**
 * Helper for ordered and unordered lists
 */
class HtmlList extends AbstractHtmlElement
{
    /**
     * Generates a 'List' element.
     *
     * @param  array $items   Array with the elements of the list
     * @param  bool  $ordered Specifies ordered/unordered list; default unordered
     * @param  array $attribs Attributes for the ol/ul tag.
     * @param  bool  $escape  Escape the items.
     * @return string The list XHTML.
     */
    public function __invoke(array $items, $ordered = false, $attribs = false, $escape = true)
    {
        $list = '';

        foreach ($items as $item) {
            if (!is_array($item)) {
                if ($escape) {
                    $escaper = $this->getView()->plugin('escapeHtml');
                    $item    = $escaper($item);
                }
                $list .= '<li>' . $item . '</li>' . self::EOL;
            } else {
                $itemLength = 5 + strlen(self::EOL);
                if ($itemLength < strlen($list)) {
                    $list = substr($list, 0, strlen($list) - $itemLength)
                     . $this($item, $ordered, $attribs, $escape) . '</li>' . self::EOL;
                } else {
                    $list .= '<li>' . $this($item, $ordered, $attribs, $escape) . '</li>' . self::EOL;
                }
            }
        }

        if ($attribs) {
            $attribs = $this->htmlAttribs($attribs);
        } else {
            $attribs = '';
        }

        $tag = ($ordered) ? 'ol' : 'ul';

        return '<' . $tag . $attribs . '>' . self::EOL . $list . '</' . $tag . '>' . self::EOL;
    }
}
