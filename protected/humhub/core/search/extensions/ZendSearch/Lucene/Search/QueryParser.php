<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Search;

use ZendSearch\Lucene;
use ZendSearch\Lucene\Analysis\Analyzer;
use ZendSearch\Lucene\Exception\RuntimeException;
use ZendSearch\Lucene\Index;
use ZendSearch\Lucene\Search\Exception\QueryParserException;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 */
class QueryParser extends Lucene\AbstractFSM
{
    /**
     * Parser instance
     *
     * @var \ZendSearch\Lucene\Search\QueryParser
     */
    private static $_instance = null;


    /**
     * Query lexer
     *
     * @var \ZendSearch\Lucene\Search\QueryLexer
     */
    private $_lexer;

    /**
     * Tokens list
     * Array of Zend_Search_Lucene_Search_QueryToken objects
     *
     * @var array
     */
    private $_tokens;

    /**
     * Current token
     *
     * @var integer|string
     */
    private $_currentToken;

    /**
     * Last token
     *
     * It can be processed within FSM states, but this addirional state simplifies FSM
     *
     * @var \ZendSearch\Lucene\Search\QueryToken
     */
    private $_lastToken = null;

    /**
     * Range query first term
     *
     * @var string
     */
    private $_rqFirstTerm = null;

    /**
     * Current query parser context
     *
     * @var \ZendSearch\Lucene\Search\QueryParserContext
     */
    private $_context;

    /**
     * Context stack
     *
     * @var array
     */
    private $_contextStack;

    /**
     * Query string encoding
     *
     * @var string
     */
    private $_encoding;

    /**
     * Query string default encoding
     *
     * @var string
     */
    private $_defaultEncoding = '';

    /**
     * Defines query parsing mode.
     *
     * If this option is turned on, then query parser suppress query parser exceptions
     * and constructs multi-term query using all words from a query.
     *
     * That helps to avoid exceptions caused by queries, which don't conform to query language,
     * but limits possibilities to check, that query entered by user has some inconsistencies.
     *
     *
     * Default is true.
     *
     * Use {@link Zend_Search_Lucene::suppressQueryParsingExceptions()},
     * {@link Zend_Search_Lucene::dontSuppressQueryParsingExceptions()} and
     * {@link Zend_Search_Lucene::checkQueryParsingExceptionsSuppressMode()} to operate
     * with this setting.
     *
     * @var boolean
     */
    private $_suppressQueryParsingExceptions = true;

    /**
     * Boolean operators constants
     */
    const B_OR  = 0;
    const B_AND = 1;

    /**
     * Default boolean queries operator
     *
     * @var integer
     */
    private $_defaultOperator = self::B_OR;


    /** Query parser State Machine states */
    const ST_COMMON_QUERY_ELEMENT       = 0;   // Terms, phrases, operators
    const ST_CLOSEDINT_RQ_START         = 1;   // Range query start (closed interval) - '['
    const ST_CLOSEDINT_RQ_FIRST_TERM    = 2;   // First term in '[term1 to term2]' construction
    const ST_CLOSEDINT_RQ_TO_TERM       = 3;   // 'TO' lexeme in '[term1 to term2]' construction
    const ST_CLOSEDINT_RQ_LAST_TERM     = 4;   // Second term in '[term1 to term2]' construction
    const ST_CLOSEDINT_RQ_END           = 5;   // Range query end (closed interval) - ']'
    const ST_OPENEDINT_RQ_START         = 6;   // Range query start (opened interval) - '{'
    const ST_OPENEDINT_RQ_FIRST_TERM    = 7;   // First term in '{term1 to term2}' construction
    const ST_OPENEDINT_RQ_TO_TERM       = 8;   // 'TO' lexeme in '{term1 to term2}' construction
    const ST_OPENEDINT_RQ_LAST_TERM     = 9;   // Second term in '{term1 to term2}' construction
    const ST_OPENEDINT_RQ_END           = 10;  // Range query end (opened interval) - '}'

    /**
     * Parser constructor
     */
    public function __construct()
    {
        parent::__construct(array(self::ST_COMMON_QUERY_ELEMENT,
                                  self::ST_CLOSEDINT_RQ_START,
                                  self::ST_CLOSEDINT_RQ_FIRST_TERM,
                                  self::ST_CLOSEDINT_RQ_TO_TERM,
                                  self::ST_CLOSEDINT_RQ_LAST_TERM,
                                  self::ST_CLOSEDINT_RQ_END,
                                  self::ST_OPENEDINT_RQ_START,
                                  self::ST_OPENEDINT_RQ_FIRST_TERM,
                                  self::ST_OPENEDINT_RQ_TO_TERM,
                                  self::ST_OPENEDINT_RQ_LAST_TERM,
                                  self::ST_OPENEDINT_RQ_END
                                 ),
                            QueryToken::getTypes());

        $this->addRules(
             array(array(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_WORD,             self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_PHRASE,           self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_FIELD,            self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_REQUIRED,         self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_PROHIBITED,       self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_FUZZY_PROX_MARK,  self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_BOOSTING_MARK,    self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_RANGE_INCL_START, self::ST_CLOSEDINT_RQ_START),
                   array(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_RANGE_EXCL_START, self::ST_OPENEDINT_RQ_START),
                   array(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_SUBQUERY_START,   self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_SUBQUERY_END,     self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_AND_LEXEME,       self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_OR_LEXEME,        self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_NOT_LEXEME,       self::ST_COMMON_QUERY_ELEMENT),
                   array(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_NUMBER,           self::ST_COMMON_QUERY_ELEMENT)
                  ));
        $this->addRules(
             array(array(self::ST_CLOSEDINT_RQ_START,      QueryToken::TT_WORD,           self::ST_CLOSEDINT_RQ_FIRST_TERM),
                   array(self::ST_CLOSEDINT_RQ_FIRST_TERM, QueryToken::TT_TO_LEXEME,      self::ST_CLOSEDINT_RQ_TO_TERM),
                   array(self::ST_CLOSEDINT_RQ_TO_TERM,    QueryToken::TT_WORD,           self::ST_CLOSEDINT_RQ_LAST_TERM),
                   array(self::ST_CLOSEDINT_RQ_LAST_TERM,  QueryToken::TT_RANGE_INCL_END, self::ST_COMMON_QUERY_ELEMENT)
                  ));
        $this->addRules(
             array(array(self::ST_OPENEDINT_RQ_START,      QueryToken::TT_WORD,           self::ST_OPENEDINT_RQ_FIRST_TERM),
                   array(self::ST_OPENEDINT_RQ_FIRST_TERM, QueryToken::TT_TO_LEXEME,      self::ST_OPENEDINT_RQ_TO_TERM),
                   array(self::ST_OPENEDINT_RQ_TO_TERM,    QueryToken::TT_WORD,           self::ST_OPENEDINT_RQ_LAST_TERM),
                   array(self::ST_OPENEDINT_RQ_LAST_TERM,  QueryToken::TT_RANGE_EXCL_END, self::ST_COMMON_QUERY_ELEMENT)
                  ));



        $addTermEntryAction             = new Lucene\FSMAction($this, 'addTermEntry');
        $addPhraseEntryAction           = new Lucene\FSMAction($this, 'addPhraseEntry');
        $setFieldAction                 = new Lucene\FSMAction($this, 'setField');
        $setSignAction                  = new Lucene\FSMAction($this, 'setSign');
        $setFuzzyProxAction             = new Lucene\FSMAction($this, 'processFuzzyProximityModifier');
        $processModifierParameterAction = new Lucene\FSMAction($this, 'processModifierParameter');
        $subqueryStartAction            = new Lucene\FSMAction($this, 'subqueryStart');
        $subqueryEndAction              = new Lucene\FSMAction($this, 'subqueryEnd');
        $logicalOperatorAction          = new Lucene\FSMAction($this, 'logicalOperator');
        $openedRQFirstTermAction        = new Lucene\FSMAction($this, 'openedRQFirstTerm');
        $openedRQLastTermAction         = new Lucene\FSMAction($this, 'openedRQLastTerm');
        $closedRQFirstTermAction        = new Lucene\FSMAction($this, 'closedRQFirstTerm');
        $closedRQLastTermAction         = new Lucene\FSMAction($this, 'closedRQLastTerm');


        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_WORD,            $addTermEntryAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_PHRASE,          $addPhraseEntryAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_FIELD,           $setFieldAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_REQUIRED,        $setSignAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_PROHIBITED,      $setSignAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_FUZZY_PROX_MARK, $setFuzzyProxAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_NUMBER,          $processModifierParameterAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_SUBQUERY_START,  $subqueryStartAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_SUBQUERY_END,    $subqueryEndAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_AND_LEXEME,      $logicalOperatorAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_OR_LEXEME,       $logicalOperatorAction);
        $this->addInputAction(self::ST_COMMON_QUERY_ELEMENT, QueryToken::TT_NOT_LEXEME,      $logicalOperatorAction);

        $this->addEntryAction(self::ST_OPENEDINT_RQ_FIRST_TERM, $openedRQFirstTermAction);
        $this->addEntryAction(self::ST_OPENEDINT_RQ_LAST_TERM,  $openedRQLastTermAction);
        $this->addEntryAction(self::ST_CLOSEDINT_RQ_FIRST_TERM, $closedRQFirstTermAction);
        $this->addEntryAction(self::ST_CLOSEDINT_RQ_LAST_TERM,  $closedRQLastTermAction);


        $this->_lexer = new QueryLexer();
    }

    /**
     * Get query parser instance
     *
     * @return \ZendSearch\Lucene\Search\QueryParser
     */
    private static function _getInstance()
    {
        if (self::$_instance === null) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    /**
     * Set query string default encoding
     *
     * @param string $encoding
     */
    public static function setDefaultEncoding($encoding)
    {
        self::_getInstance()->_defaultEncoding = $encoding;
    }

    /**
     * Get query string default encoding
     *
     * @return string
     */
    public static function getDefaultEncoding()
    {
       return self::_getInstance()->_defaultEncoding;
    }

    /**
     * Set default boolean operator
     *
     * @param integer $operator
     */
    public static function setDefaultOperator($operator)
    {
        self::_getInstance()->_defaultOperator = $operator;
    }

    /**
     * Get default boolean operator
     *
     * @return integer
     */
    public static function getDefaultOperator()
    {
        return self::_getInstance()->_defaultOperator;
    }

    /**
     * Turn on 'suppress query parser exceptions' mode.
     */
    public static function suppressQueryParsingExceptions()
    {
        self::_getInstance()->_suppressQueryParsingExceptions = true;
    }
    /**
     * Turn off 'suppress query parser exceptions' mode.
     */
    public static function dontSuppressQueryParsingExceptions()
    {
        self::_getInstance()->_suppressQueryParsingExceptions = false;
    }
    /**
     * Check 'suppress query parser exceptions' mode.
     * @return boolean
     */
    public static function queryParsingExceptionsSuppressed()
    {
        return self::_getInstance()->_suppressQueryParsingExceptions;
    }


    /**
     * Escape keyword to force it to be parsed as one term
     *
     * @param string $keyword
     * @return string
     */
    public static function escape($keyword)
    {
        return '\\' . implode('\\', str_split($keyword));
    }

    /**
     * Parses a query string
     *
     * @param string $strQuery
     * @param string $encoding
     * @throws \ZendSearch\Lucene\Search\Exception\QueryParserException
     * @throws \ZendSearch\Lucene\Exception\RuntimeException
     * @return \ZendSearch\Lucene\Search\Query\AbstractQuery
     */
    public static function parse($strQuery, $encoding = null)
    {
        self::_getInstance();

        // Reset FSM if previous parse operation didn't return it into a correct state
        self::$_instance->reset();

        try {
            self::$_instance->_encoding     = ($encoding !== null) ? $encoding : self::$_instance->_defaultEncoding;
            self::$_instance->_lastToken    = null;
            self::$_instance->_context      = new QueryParserContext(self::$_instance->_encoding);
            self::$_instance->_contextStack = array();
            self::$_instance->_tokens       = self::$_instance->_lexer->tokenize($strQuery, self::$_instance->_encoding);

            // Empty query
            if (count(self::$_instance->_tokens) == 0) {
                return new Query\Insignificant();
            }


            foreach (self::$_instance->_tokens as $token) {
                try {
                    self::$_instance->_currentToken = $token;
                    self::$_instance->process($token->type);

                    self::$_instance->_lastToken = $token;
                } catch (\Exception $e) {
                    if (strpos($e->getMessage(), 'There is no any rule for') !== false) {
                        throw new QueryParserException( 'Syntax error at char position ' . $token->position . '.', 0, $e);
                    }

                    throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
                }
            }

            if (count(self::$_instance->_contextStack) != 0) {
                throw new QueryParserException('Syntax Error: mismatched parentheses, every opening must have closing.' );
            }

            return self::$_instance->_context->getQuery();
        } catch (QueryParserException $e) {
            if (self::$_instance->_suppressQueryParsingExceptions) {
                $queryTokens = Analyzer\Analyzer::getDefault()->tokenize($strQuery, self::$_instance->_encoding);

                $query = new Query\MultiTerm();
                $termsSign = (self::$_instance->_defaultOperator == self::B_AND) ? true /* required term */ :
                                                                                   null /* optional term */;

                foreach ($queryTokens as $token) {
                    $query->addTerm(new Index\Term($token->getTermText()), $termsSign);
                }


                return $query;
            } else {
                throw new RuntimeException($e->getMessage(), $e->getCode(), $e);
            }
        }
    }

    /*********************************************************************
     * Actions implementation
     *
     * Actions affect on recognized lexemes list
     *********************************************************************/

    /**
     * Add term to a query
     */
    public function addTermEntry()
    {
        $entry = new QueryEntry\Term($this->_currentToken->text, $this->_context->getField());
        $this->_context->addEntry($entry);
    }

    /**
     * Add phrase to a query
     */
    public function addPhraseEntry()
    {
        $entry = new QueryEntry\Phrase($this->_currentToken->text, $this->_context->getField());
        $this->_context->addEntry($entry);
    }

    /**
     * Set entry field
     */
    public function setField()
    {
        $this->_context->setNextEntryField($this->_currentToken->text);
    }

    /**
     * Set entry sign
     */
    public function setSign()
    {
        $this->_context->setNextEntrySign($this->_currentToken->type);
    }


    /**
     * Process fuzzy search/proximity modifier - '~'
     */
    public function processFuzzyProximityModifier()
    {
        $this->_context->processFuzzyProximityModifier();
    }

    /**
     * Process modifier parameter
     *
     * @throws \ZendSearch\Lucene\Search\Exception\QueryParserException
     * @throws \ZendSearch\Lucene\Exception\RuntimeException
     */
    public function processModifierParameter()
    {
        if ($this->_lastToken === null) {
            throw new QueryParserException('Lexeme modifier parameter must follow lexeme modifier. Char position 0.' );
        }

        switch ($this->_lastToken->type) {
            case QueryToken::TT_FUZZY_PROX_MARK:
                $this->_context->processFuzzyProximityModifier($this->_currentToken->text);
                break;

            case QueryToken::TT_BOOSTING_MARK:
                $this->_context->boost($this->_currentToken->text);
                break;

            default:
                // It's not a user input exception
                throw new RuntimeException('Lexeme modifier parameter must follow lexeme modifier. Char position 0.' );
        }
    }


    /**
     * Start subquery
     */
    public function subqueryStart()
    {
        $this->_contextStack[] = $this->_context;
        $this->_context        = new QueryParserContext($this->_encoding, $this->_context->getField());
    }

    /**
     * End subquery
     */
    public function subqueryEnd()
    {
        if (count($this->_contextStack) == 0) {
            throw new QueryParserException('Syntax Error: mismatched parentheses, every opening must have closing. Char position ' . $this->_currentToken->position . '.' );
        }

        $query          = $this->_context->getQuery();
        $this->_context = array_pop($this->_contextStack);

        $this->_context->addEntry(new QueryEntry\Subquery($query));
    }

    /**
     * Process logical operator
     */
    public function logicalOperator()
    {
        $this->_context->addLogicalOperator($this->_currentToken->type);
    }

    /**
     * Process first range query term (opened interval)
     */
    public function openedRQFirstTerm()
    {
        $this->_rqFirstTerm = $this->_currentToken->text;
    }

    /**
     * Process last range query term (opened interval)
     *
     * @throws \ZendSearch\Lucene\Search\Exception\QueryParserException
     */
    public function openedRQLastTerm()
    {
        $tokens = Analyzer\Analyzer::getDefault()->tokenize($this->_rqFirstTerm, $this->_encoding);
        if (count($tokens) > 1) {
            throw new QueryParserException('Range query boundary terms must be non-multiple word terms');
        } elseif (count($tokens) == 1) {
            $from = new Index\Term(reset($tokens)->getTermText(), $this->_context->getField());
        } else {
            $from = null;
        }

        $tokens = Analyzer\Analyzer::getDefault()->tokenize($this->_currentToken->text, $this->_encoding);
        if (count($tokens) > 1) {
            throw new QueryParserException('Range query boundary terms must be non-multiple word terms');
        } elseif (count($tokens) == 1) {
            $to = new Index\Term(reset($tokens)->getTermText(), $this->_context->getField());
        } else {
            $to = null;
        }

        if ($from === null  &&  $to === null) {
            throw new QueryParserException('At least one range query boundary term must be non-empty term');
        }

        $rangeQuery = new Query\Range($from, $to, false);
        $entry      = new QueryEntry\Subquery($rangeQuery);
        $this->_context->addEntry($entry);
    }

    /**
     * Process first range query term (closed interval)
     */
    public function closedRQFirstTerm()
    {
        $this->_rqFirstTerm = $this->_currentToken->text;
    }

    /**
     * Process last range query term (closed interval)
     *
     * @throws \ZendSearch\Lucene\Search\Exception\QueryParserException
     */
    public function closedRQLastTerm()
    {
        $tokens = Analyzer\Analyzer::getDefault()->tokenize($this->_rqFirstTerm, $this->_encoding);
        if (count($tokens) > 1) {
            throw new QueryParserException('Range query boundary terms must be non-multiple word terms');
        } elseif (count($tokens) == 1) {
            $from = new Index\Term(reset($tokens)->getTermText(), $this->_context->getField());
        } else {
            $from = null;
        }

        $tokens = Analyzer\Analyzer::getDefault()->tokenize($this->_currentToken->text, $this->_encoding);
        if (count($tokens) > 1) {
            throw new QueryParserException('Range query boundary terms must be non-multiple word terms');
        } elseif (count($tokens) == 1) {
            $to = new Index\Term(reset($tokens)->getTermText(), $this->_context->getField());
        } else {
            $to = null;
        }

        if ($from === null  &&  $to === null) {
            throw new QueryParserException('At least one range query boundary term must be non-empty term');
        }

        $rangeQuery = new Query\Range($from, $to, true);
        $entry      = new QueryEntry\Subquery($rangeQuery);
        $this->_context->addEntry($entry);
    }
}
