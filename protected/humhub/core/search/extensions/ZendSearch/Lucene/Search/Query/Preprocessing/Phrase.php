<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Search\Query\Preprocessing;

use ZendSearch\Lucene;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Analysis\Analyzer\AnalyzerInterface;
use ZendSearch\Lucene\Index;
use ZendSearch\Lucene\Search\Highlighter\HighlighterInterface as Highlighter;
use ZendSearch\Lucene\Search\Query;

/**
 * It's an internal abstract class intended to finalize ase a query processing after query parsing.
 * This type of query is not actually involved into query execution.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 * @internal
 */
class Phrase extends AbstractPreprocessing
{
    /**
     * Phrase to find.
     *
     * @var string
     */
    private $_phrase;

    /**
     * Phrase encoding (field name is always provided using UTF-8 encoding since it may be retrieved from index).
     *
     * @var string
     */
    private $_phraseEncoding;


    /**
     * Field name.
     *
     * @var string
     */
    private $_field;

    /**
     * Sets the number of other words permitted between words in query phrase.
     * If zero, then this is an exact phrase search.  For larger values this works
     * like a WITHIN or NEAR operator.
     *
     * The slop is in fact an edit-distance, where the units correspond to
     * moves of terms in the query phrase out of position.  For example, to switch
     * the order of two words requires two moves (the first move places the words
     * atop one another), so to permit re-orderings of phrases, the slop must be
     * at least two.
     * More exact matches are scored higher than sloppier matches, thus search
     * results are sorted by exactness.
     *
     * The slop is zero by default, requiring exact matches.
     *
     * @var integer
     */
    private $_slop;

    /**
     * Class constructor.  Create a new preprocessing object for prase query.
     *
     * @param string $phrase          Phrase to search.
     * @param string $phraseEncoding  Phrase encoding.
     * @param string $fieldName       Field name.
     */
    public function __construct($phrase, $phraseEncoding, $fieldName)
    {
        $this->_phrase         = $phrase;
        $this->_phraseEncoding = $phraseEncoding;
        $this->_field          = $fieldName;
    }

    /**
     * Set slop
     *
     * @param integer $slop
     */
    public function setSlop($slop)
    {
        $this->_slop = $slop;
    }


    /**
     * Get slop
     *
     * @return integer
     */
    public function getSlop()
    {
        return $this->_slop;
    }

    /**
     * Re-write query into primitive queries in the context of specified index
     *
     * @param \ZendSearch\Lucene\SearchIndexInterface $index
     * @return \ZendSearch\Lucene\Search\Query\AbstractQuery
     */
    public function rewrite(Lucene\SearchIndexInterface $index)
    {
// Allow to use wildcards within phrases
// They are either removed by text analyzer or used as a part of keyword for keyword fields
//
//        if (strpos($this->_phrase, '?') !== false || strpos($this->_phrase, '*') !== false) {
//            require_once 'Zend/Search/Lucene/Search/QueryParserException.php';
//            throw new Zend_Search_Lucene_Search_QueryParserException('Wildcards are only allowed in a single terms.');
//        }

        // Split query into subqueries if field name is not specified
        if ($this->_field === null) {
            $query = new Query\Boolean();
            $query->setBoost($this->getBoost());

            if (Lucene\Lucene::getDefaultSearchField() === null) {
                $searchFields = $index->getFieldNames(true);
            } else {
                $searchFields = array(Lucene\Lucene::getDefaultSearchField());
            }

            foreach ($searchFields as $fieldName) {
                $subquery = new Phrase($this->_phrase,
                                       $this->_phraseEncoding,
                                       $fieldName);
                $subquery->setSlop($this->getSlop());

                $query->addSubquery($subquery->rewrite($index));
            }

            $this->_matches = $query->getQueryTerms();
            return $query;
        }

        // Recognize exact term matching (it corresponds to Keyword fields stored in the index)
        // encoding is not used since we expect binary matching
        $term = new Index\Term($this->_phrase, $this->_field);
        if ($index->hasTerm($term)) {
            $query = new Query\Term($term);
            $query->setBoost($this->getBoost());

            $this->_matches = $query->getQueryTerms();
            return $query;
        }


        // tokenize phrase using current analyzer and process it as a phrase query
        $tokens = Analyzer::getDefault()->tokenize($this->_phrase, $this->_phraseEncoding);

        if (count($tokens) == 0) {
            $this->_matches = array();
            return new Query\Insignificant();
        }

        if (count($tokens) == 1) {
            $term  = new Index\Term($tokens[0]->getTermText(), $this->_field);
            $query = new Query\Term($term);
            $query->setBoost($this->getBoost());

            $this->_matches = $query->getQueryTerms();
            return $query;
        }

        //It's non-trivial phrase query
        $position = -1;
        $query = new Query\Phrase();
        foreach ($tokens as $token) {
            $position += $token->getPositionIncrement();
            $term = new Index\Term($token->getTermText(), $this->_field);
            $query->addTerm($term, $position);
            $query->setSlop($this->getSlop());
        }
        $this->_matches = $query->getQueryTerms();
        return $query;
    }

    /**
     * Query specific matches highlighting
     *
     * @param Highlighter $highlighter  Highlighter object (also contains doc for highlighting)
     */
    protected function _highlightMatches(Highlighter $highlighter)
    {
        /** Skip fields detection. We don't need it, since we expect all fields presented in the HTML body and don't differentiate them */

        /** Skip exact term matching recognition, keyword fields highlighting is not supported */

        /** Skip wildcard queries recognition. Supported wildcards are removed by text analyzer */


        // tokenize phrase using current analyzer and process it as a phrase query
        $tokens = Analyzer::getDefault()->tokenize($this->_phrase, $this->_phraseEncoding);

        if (count($tokens) == 0) {
            // Do nothing
            return;
        }

        if (count($tokens) == 1) {
            $highlighter->highlight($tokens[0]->getTermText());
            return;
        }

        //It's non-trivial phrase query
        $words = array();
        foreach ($tokens as $token) {
            $words[] = $token->getTermText();
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
        if ($this->_field !== null) {
            $query = $this->_field . ':';
        } else {
            $query = '';
        }

        $query .= '"' . $this->_phrase . '"';

        if ($this->_slop != 0) {
            $query .= '~' . $this->_slop;
        }

        if ($this->getBoost() != 1) {
            $query .= '^' . round($this->getBoost(), 4);
        }

        return $query;
    }
}
