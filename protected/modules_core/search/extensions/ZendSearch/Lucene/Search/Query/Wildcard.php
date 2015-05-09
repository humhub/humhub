<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Search\Query;

use ZendSearch\Lucene;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Analysis\Analyzer\AnalyzerInterface;
use ZendSearch\Lucene\Exception\OutOfBoundsException;
use ZendSearch\Lucene\Exception\RuntimeException;
use ZendSearch\Lucene\Exception\UnsupportedMethodCallException;
use ZendSearch\Lucene\Index;
use ZendSearch\Lucene\Search\Highlighter\HighlighterInterface as Highlighter;
use Zend\Stdlib\ErrorHandler;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 */
class Wildcard extends AbstractQuery
{
    /**
     * Search pattern.
     *
     * Field has to be fully specified or has to be null
     * Text may contain '*' or '?' symbols
     *
     * @var \ZendSearch\Lucene\Index\Term
     */
    private $_pattern;

    /**
     * Matched terms.
     *
     * Matched terms list.
     * It's filled during the search (rewrite operation) and may be used for search result
     * post-processing
     *
     * Array of Zend_Search_Lucene_Index_Term objects
     *
     * @var array
     */
    private $_matches = null;

    /**
     * Minimum term prefix length (number of minimum non-wildcard characters)
     *
     * @var integer
     */
    private static $_minPrefixLength = 3;

    /**
     * Zend_Search_Lucene_Search_Query_Wildcard constructor.
     *
     * @param \ZendSearch\Lucene\Index\Term $pattern
     */
    public function __construct(Index\Term $pattern)
    {
        $this->_pattern = $pattern;
    }

    /**
     * Get minimum prefix length
     *
     * @return integer
     */
    public static function getMinPrefixLength()
    {
        return self::$_minPrefixLength;
    }

    /**
     * Set minimum prefix length
     *
     * @param integer $minPrefixLength
     */
    public static function setMinPrefixLength($minPrefixLength)
    {
        self::$_minPrefixLength = $minPrefixLength;
    }

    /**
     * Get terms prefix
     *
     * @param string $word
     * @return string
     */
    private static function _getPrefix($word)
    {
        $questionMarkPosition = strpos($word, '?');
        $astrericPosition     = strpos($word, '*');

        if ($questionMarkPosition !== false) {
            if ($astrericPosition !== false) {
                return substr($word, 0, min($questionMarkPosition, $astrericPosition));
            }

            return substr($word, 0, $questionMarkPosition);
        } elseif ($astrericPosition !== false) {
            return substr($word, 0, $astrericPosition);
        }

        return $word;
    }

    /**
     * Re-write query into primitive queries in the context of specified index
     *
     * @param \ZendSearch\Lucene\SearchIndexInterface $index
     * @throws \ZendSearch\Lucene\Exception\RuntimeException
     * @throws \ZendSearch\Lucene\Exception\OutOfBoundsException
     * @return \ZendSearch\Lucene\Search\Query\AbstractQuery
     */
    public function rewrite(Lucene\SearchIndexInterface $index)
    {
        $this->_matches = array();

        if ($this->_pattern->field === null) {
            // Search through all fields
            $fields = $index->getFieldNames(true /* indexed fields list */);
        } else {
            $fields = array($this->_pattern->field);
        }

        $prefix          = self::_getPrefix($this->_pattern->text);
        $prefixLength    = strlen($prefix);
        $matchExpression = '/^' . str_replace(array('\\?', '\\*'), array('.', '.*') , preg_quote($this->_pattern->text, '/')) . '$/';

        if ($prefixLength < self::$_minPrefixLength) {
            throw new RuntimeException(
                'At least ' . self::$_minPrefixLength . ' non-wildcard characters are required at the beginning of pattern.'
            );
        }

        /** 
         * @todo check for PCRE unicode support may be performed through Zend_Environment in some future 
         */
        ErrorHandler::start(E_WARNING);
        $result = preg_match('/\pL/u', 'a');
        ErrorHandler::stop();
        if ($result == 1) {
            // PCRE unicode support is turned on
            // add Unicode modifier to the match expression
            $matchExpression .= 'u';
        }

        $maxTerms = Lucene\Lucene::getTermsPerQueryLimit();
        foreach ($fields as $field) {
            $index->resetTermsStream();

            if ($prefix != '') {
                $index->skipTo(new Index\Term($prefix, $field));

                while ($index->currentTerm() !== null          &&
                       $index->currentTerm()->field == $field  &&
                       substr($index->currentTerm()->text, 0, $prefixLength) == $prefix) {
                    if (preg_match($matchExpression, $index->currentTerm()->text) === 1) {
                        $this->_matches[] = $index->currentTerm();

                        if ($maxTerms != 0  &&  count($this->_matches) > $maxTerms) {
                            throw new OutOfBoundsException('Terms per query limit is reached.');
                        }
                    }

                    $index->nextTerm();
                }
            } else {
                $index->skipTo(new Index\Term('', $field));

                while ($index->currentTerm() !== null  &&  $index->currentTerm()->field == $field) {
                    if (preg_match($matchExpression, $index->currentTerm()->text) === 1) {
                        $this->_matches[] = $index->currentTerm();

                        if ($maxTerms != 0  &&  count($this->_matches) > $maxTerms) {
                            throw new OutOfBoundsException('Terms per query limit is reached.');
                        }
                    }

                    $index->nextTerm();
                }
            }

            $index->closeTermsStream();
        }

        if (count($this->_matches) == 0) {
            return new EmptyResult();
        } elseif (count($this->_matches) == 1) {
            return new Term(reset($this->_matches));
        } else {
            $rewrittenQuery = new MultiTerm();

            foreach ($this->_matches as $matchedTerm) {
                $rewrittenQuery->addTerm($matchedTerm);
            }

            return $rewrittenQuery;
        }
    }

    /**
     * Optimize query in the context of specified index
     *
     * @param \ZendSearch\Lucene\SearchIndexInterface $index
     * @throws \ZendSearch\Lucene\Exception\UnsupportedMethodCallException
     * @return \ZendSearch\Lucene\Search\Query\AbstractQuery
     */
    public function optimize(Lucene\SearchIndexInterface $index)
    {
        throw new UnsupportedMethodCallException('Wildcard query should not be directly used for search. Use $query->rewrite($index)');
    }


    /**
     * Returns query pattern
     *
     * @return \ZendSearch\Lucene\Index\Term
     */
    public function getPattern()
    {
        return $this->_pattern;
    }


    /**
     * Return query terms
     *
     * @throws \ZendSearch\Lucene\Exception\RuntimeException
     * @return array
     */
    public function getQueryTerms()
    {
        if ($this->_matches === null) {
            throw new RuntimeException('Search has to be performed first to get matched terms');
        }

        return $this->_matches;
    }

    /**
     * Constructs an appropriate Weight implementation for this query.
     *
     * @param \ZendSearch\Lucene\SearchIndexInterface $reader
     * @throws \ZendSearch\Lucene\Exception\UnsupportedMethodCallException
     */
    public function createWeight(Lucene\SearchIndexInterface $reader)
    {
        throw new UnsupportedMethodCallException('Wildcard query should not be directly used for search. Use $query->rewrite($index)');
    }


    /**
     * Execute query in context of index reader
     * It also initializes necessary internal structures
     *
     * @param \ZendSearch\Lucene\SearchIndexInterface $reader
     * @param \ZendSearch\Lucene\Index\DocsFilter|null $docsFilter
     * @throws \ZendSearch\Lucene\Exception\UnsupportedMethodCallException
     */
    public function execute(Lucene\SearchIndexInterface $reader, $docsFilter = null)
    {
        throw new UnsupportedMethodCallException('Wildcard query should not be directly used for search. Use $query->rewrite($index)');
    }

    /**
     * Get document ids likely matching the query
     *
     * It's an array with document ids as keys (performance considerations)
     *
     * @throws \ZendSearch\Lucene\Exception\UnsupportedMethodCallException
     * @return array
     */
    public function matchedDocs()
    {
        throw new UnsupportedMethodCallException(
            'Wildcard query should not be directly used for search. Use $query->rewrite($index)'
        );
    }

    /**
     * Score specified document
     *
     * @param integer $docId
     * @param \ZendSearch\Lucene\SearchIndexInterface $reader
     * @throws \ZendSearch\Lucene\Exception\UnsupportedMethodCallException
     * @return float
     */
    public function score($docId, Lucene\SearchIndexInterface $reader)
    {
        throw new UnsupportedMethodCallException(
            'Wildcard query should not be directly used for search. Use $query->rewrite($index)'
        );
    }

    /**
     * Query specific matches highlighting
     *
     * @param Highlighter $highlighter  Highlighter object (also contains doc for highlighting)
     */
    protected function _highlightMatches(Highlighter $highlighter)
    {
        $words = array();

        $matchExpression = '/^' . str_replace(array('\\?', '\\*'), array('.', '.*') , preg_quote($this->_pattern->text, '/')) . '$/';
        ErrorHandler::start(E_WARNING);
        $result = preg_match('/\pL/u', 'a');
        ErrorHandler::stop();
        if ($result == 1) {
            // PCRE unicode support is turned on
            // add Unicode modifier to the match expression
            $matchExpression .= 'u';
        }

        $docBody = $highlighter->getDocument()->getFieldUtf8Value('body');
        $tokens = Analyzer::getDefault()->tokenize($docBody, 'UTF-8');
        foreach ($tokens as $token) {
            if (preg_match($matchExpression, $token->getTermText()) === 1) {
                $words[] = $token->getTermText();
            }
        }

        $highlighter->highlight($words);
    }

    /**
     * Print a query
     *
     * @return string
     */
    public function __toString()
    {
        // It's used only for query visualisation, so we don't care about characters escaping
        if ($this->_pattern->field !== null) {
            $query = $this->_pattern->field . ':';
        } else {
            $query = '';
        }

        $query .= $this->_pattern->text;

        if ($this->getBoost() != 1) {
            $query = $query . '^' . round($this->getBoost(), 4);
        }

        return $query;
    }
}
