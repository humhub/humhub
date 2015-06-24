<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Search\Highlighter;

use ZendSearch\Lucene\Document;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 */
class DefaultHighlighter implements HighlighterInterface
{
    /**
     * List of colors for text highlighting
     *
     * @var array
     */
    protected $_highlightColors = array('#66ffff', '#ff66ff', '#ffff66',
                                        '#ff8888', '#88ff88', '#8888ff',
                                        '#88dddd', '#dd88dd', '#dddd88',
                                        '#aaddff', '#aaffdd', '#ddaaff',
                                        '#ddffaa', '#ffaadd', '#ffddaa');

    /**
     * Index of current color for highlighting
     *
     * Index is increased at each highlight() call, so terms matching different queries are highlighted using different colors.
     *
     * @var integer
     */
    protected $_currentColorIndex = 0;

    /**
     * HTML document for highlighting
     *
     * @var \ZendSearch\Lucene\Document\HTML
     */
    protected $_doc;

    /**
     * Set document for highlighting.
     *
     * @param \ZendSearch\Lucene\Document\HTML $document
     */
    public function setDocument(Document\HTML $document)
    {
        $this->_doc = $document;
    }

    /**
     * Get document for highlighting.
     *
     * @return \ZendSearch\Lucene\Document\HTML $document
     */
    public function getDocument()
    {
        return $this->_doc;
    }

    /**
     * Highlight specified words
     *
     * @param string|array $words  Words to highlight. They could be organized using the array or string.
     */
    public function highlight($words)
    {
        $color = $this->_highlightColors[$this->_currentColorIndex];
        $this->_currentColorIndex = ($this->_currentColorIndex + 1) % count($this->_highlightColors);

        $this->_doc->highlight($words, $color);
    }
}
