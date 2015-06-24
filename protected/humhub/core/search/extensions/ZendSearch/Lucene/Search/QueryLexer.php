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
use ZendSearch\Lucene\Search\Exception\QueryParserException;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Search
 */
class QueryLexer extends Lucene\AbstractFSM
{
    /** State Machine states */
    const ST_WHITE_SPACE     = 0;
    const ST_SYNT_LEXEME     = 1;
    const ST_LEXEME          = 2;
    const ST_QUOTED_LEXEME   = 3;
    const ST_ESCAPED_CHAR    = 4;
    const ST_ESCAPED_QCHAR   = 5;
    const ST_LEXEME_MODIFIER = 6;
    const ST_NUMBER          = 7;
    const ST_MANTISSA        = 8;
    const ST_ERROR           = 9;

    /** Input symbols */
    const IN_WHITE_SPACE     = 0;
    const IN_SYNT_CHAR       = 1;
    const IN_LEXEME_MODIFIER = 2;
    const IN_ESCAPE_CHAR     = 3;
    const IN_QUOTE           = 4;
    const IN_DECIMAL_POINT   = 5;
    const IN_ASCII_DIGIT     = 6;
    const IN_CHAR            = 7;
    const IN_MUTABLE_CHAR    = 8;

    const QUERY_WHITE_SPACE_CHARS      = " \n\r\t";
    const QUERY_SYNT_CHARS             = ':()[]{}!|&';
    const QUERY_MUTABLE_CHARS          = '+-';
    const QUERY_DOUBLECHARLEXEME_CHARS = '|&';
    const QUERY_LEXEMEMODIFIER_CHARS   = '~^';
    const QUERY_ASCIIDIGITS_CHARS      = '0123456789';

    /**
     * List of recognized lexemes
     *
     * @var array
     */
    private $_lexemes;

    /**
     * Query string (array of single- or non single-byte characters)
     *
     * @var array
     */
    private $_queryString;

    /**
     * Current position within a query string
     * Used to create appropriate error messages
     *
     * @var integer
     */
    private $_queryStringPosition;

    /**
     * Recognized part of current lexeme
     *
     * @var string
     */
    private $_currentLexeme;

    public function __construct()
    {
        parent::__construct( array(self::ST_WHITE_SPACE,
                                   self::ST_SYNT_LEXEME,
                                   self::ST_LEXEME,
                                   self::ST_QUOTED_LEXEME,
                                   self::ST_ESCAPED_CHAR,
                                   self::ST_ESCAPED_QCHAR,
                                   self::ST_LEXEME_MODIFIER,
                                   self::ST_NUMBER,
                                   self::ST_MANTISSA,
                                   self::ST_ERROR),
                             array(self::IN_WHITE_SPACE,
                                   self::IN_SYNT_CHAR,
                                   self::IN_MUTABLE_CHAR,
                                   self::IN_LEXEME_MODIFIER,
                                   self::IN_ESCAPE_CHAR,
                                   self::IN_QUOTE,
                                   self::IN_DECIMAL_POINT,
                                   self::IN_ASCII_DIGIT,
                                   self::IN_CHAR));


        $lexemeModifierErrorAction    = new Lucene\FSMAction($this, 'lexModifierErrException');
        $quoteWithinLexemeErrorAction = new Lucene\FSMAction($this, 'quoteWithinLexemeErrException');
        $wrongNumberErrorAction       = new Lucene\FSMAction($this, 'wrongNumberErrException');



        $this->addRules(array( array(self::ST_WHITE_SPACE,   self::IN_WHITE_SPACE,     self::ST_WHITE_SPACE),
                               array(self::ST_WHITE_SPACE,   self::IN_SYNT_CHAR,       self::ST_SYNT_LEXEME),
                               array(self::ST_WHITE_SPACE,   self::IN_MUTABLE_CHAR,    self::ST_SYNT_LEXEME),
                               array(self::ST_WHITE_SPACE,   self::IN_LEXEME_MODIFIER, self::ST_LEXEME_MODIFIER),
                               array(self::ST_WHITE_SPACE,   self::IN_ESCAPE_CHAR,     self::ST_ESCAPED_CHAR),
                               array(self::ST_WHITE_SPACE,   self::IN_QUOTE,           self::ST_QUOTED_LEXEME),
                               array(self::ST_WHITE_SPACE,   self::IN_DECIMAL_POINT,   self::ST_LEXEME),
                               array(self::ST_WHITE_SPACE,   self::IN_ASCII_DIGIT,     self::ST_LEXEME),
                               array(self::ST_WHITE_SPACE,   self::IN_CHAR,            self::ST_LEXEME)
                             ));
        $this->addRules(array( array(self::ST_SYNT_LEXEME,   self::IN_WHITE_SPACE,     self::ST_WHITE_SPACE),
                               array(self::ST_SYNT_LEXEME,   self::IN_SYNT_CHAR,       self::ST_SYNT_LEXEME),
                               array(self::ST_SYNT_LEXEME,   self::IN_MUTABLE_CHAR,    self::ST_SYNT_LEXEME),
                               array(self::ST_SYNT_LEXEME,   self::IN_LEXEME_MODIFIER, self::ST_LEXEME_MODIFIER),
                               array(self::ST_SYNT_LEXEME,   self::IN_ESCAPE_CHAR,     self::ST_ESCAPED_CHAR),
                               array(self::ST_SYNT_LEXEME,   self::IN_QUOTE,           self::ST_QUOTED_LEXEME),
                               array(self::ST_SYNT_LEXEME,   self::IN_DECIMAL_POINT,   self::ST_LEXEME),
                               array(self::ST_SYNT_LEXEME,   self::IN_ASCII_DIGIT,     self::ST_LEXEME),
                               array(self::ST_SYNT_LEXEME,   self::IN_CHAR,            self::ST_LEXEME)
                             ));
        $this->addRules(array( array(self::ST_LEXEME,        self::IN_WHITE_SPACE,     self::ST_WHITE_SPACE),
                               array(self::ST_LEXEME,        self::IN_SYNT_CHAR,       self::ST_SYNT_LEXEME),
                               array(self::ST_LEXEME,        self::IN_MUTABLE_CHAR,    self::ST_LEXEME),
                               array(self::ST_LEXEME,        self::IN_LEXEME_MODIFIER, self::ST_LEXEME_MODIFIER),
                               array(self::ST_LEXEME,        self::IN_ESCAPE_CHAR,     self::ST_ESCAPED_CHAR),

                               // IN_QUOTE     not allowed
                               array(self::ST_LEXEME,        self::IN_QUOTE,           self::ST_ERROR, $quoteWithinLexemeErrorAction),

                               array(self::ST_LEXEME,        self::IN_DECIMAL_POINT,   self::ST_LEXEME),
                               array(self::ST_LEXEME,        self::IN_ASCII_DIGIT,     self::ST_LEXEME),
                               array(self::ST_LEXEME,        self::IN_CHAR,            self::ST_LEXEME)
                             ));
        $this->addRules(array( array(self::ST_QUOTED_LEXEME, self::IN_WHITE_SPACE,     self::ST_QUOTED_LEXEME),
                               array(self::ST_QUOTED_LEXEME, self::IN_SYNT_CHAR,       self::ST_QUOTED_LEXEME),
                               array(self::ST_QUOTED_LEXEME, self::IN_MUTABLE_CHAR,    self::ST_QUOTED_LEXEME),
                               array(self::ST_QUOTED_LEXEME, self::IN_LEXEME_MODIFIER, self::ST_QUOTED_LEXEME),
                               array(self::ST_QUOTED_LEXEME, self::IN_ESCAPE_CHAR,     self::ST_ESCAPED_QCHAR),
                               array(self::ST_QUOTED_LEXEME, self::IN_QUOTE,           self::ST_WHITE_SPACE),
                               array(self::ST_QUOTED_LEXEME, self::IN_DECIMAL_POINT,   self::ST_QUOTED_LEXEME),
                               array(self::ST_QUOTED_LEXEME, self::IN_ASCII_DIGIT,     self::ST_QUOTED_LEXEME),
                               array(self::ST_QUOTED_LEXEME, self::IN_CHAR,            self::ST_QUOTED_LEXEME)
                             ));
        $this->addRules(array( array(self::ST_ESCAPED_CHAR,  self::IN_WHITE_SPACE,     self::ST_LEXEME),
                               array(self::ST_ESCAPED_CHAR,  self::IN_SYNT_CHAR,       self::ST_LEXEME),
                               array(self::ST_ESCAPED_CHAR,  self::IN_MUTABLE_CHAR,    self::ST_LEXEME),
                               array(self::ST_ESCAPED_CHAR,  self::IN_LEXEME_MODIFIER, self::ST_LEXEME),
                               array(self::ST_ESCAPED_CHAR,  self::IN_ESCAPE_CHAR,     self::ST_LEXEME),
                               array(self::ST_ESCAPED_CHAR,  self::IN_QUOTE,           self::ST_LEXEME),
                               array(self::ST_ESCAPED_CHAR,  self::IN_DECIMAL_POINT,   self::ST_LEXEME),
                               array(self::ST_ESCAPED_CHAR,  self::IN_ASCII_DIGIT,     self::ST_LEXEME),
                               array(self::ST_ESCAPED_CHAR,  self::IN_CHAR,            self::ST_LEXEME)
                             ));
        $this->addRules(array( array(self::ST_ESCAPED_QCHAR, self::IN_WHITE_SPACE,     self::ST_QUOTED_LEXEME),
                               array(self::ST_ESCAPED_QCHAR, self::IN_SYNT_CHAR,       self::ST_QUOTED_LEXEME),
                               array(self::ST_ESCAPED_QCHAR, self::IN_MUTABLE_CHAR,    self::ST_QUOTED_LEXEME),
                               array(self::ST_ESCAPED_QCHAR, self::IN_LEXEME_MODIFIER, self::ST_QUOTED_LEXEME),
                               array(self::ST_ESCAPED_QCHAR, self::IN_ESCAPE_CHAR,     self::ST_QUOTED_LEXEME),
                               array(self::ST_ESCAPED_QCHAR, self::IN_QUOTE,           self::ST_QUOTED_LEXEME),
                               array(self::ST_ESCAPED_QCHAR, self::IN_DECIMAL_POINT,   self::ST_QUOTED_LEXEME),
                               array(self::ST_ESCAPED_QCHAR, self::IN_ASCII_DIGIT,     self::ST_QUOTED_LEXEME),
                               array(self::ST_ESCAPED_QCHAR, self::IN_CHAR,            self::ST_QUOTED_LEXEME)
                             ));
        $this->addRules(array( array(self::ST_LEXEME_MODIFIER, self::IN_WHITE_SPACE,     self::ST_WHITE_SPACE),
                               array(self::ST_LEXEME_MODIFIER, self::IN_SYNT_CHAR,       self::ST_SYNT_LEXEME),
                               array(self::ST_LEXEME_MODIFIER, self::IN_MUTABLE_CHAR,    self::ST_SYNT_LEXEME),
                               array(self::ST_LEXEME_MODIFIER, self::IN_LEXEME_MODIFIER, self::ST_LEXEME_MODIFIER),

                               // IN_ESCAPE_CHAR       not allowed
                               array(self::ST_LEXEME_MODIFIER, self::IN_ESCAPE_CHAR,     self::ST_ERROR, $lexemeModifierErrorAction),

                               // IN_QUOTE             not allowed
                               array(self::ST_LEXEME_MODIFIER, self::IN_QUOTE,           self::ST_ERROR, $lexemeModifierErrorAction),


                               array(self::ST_LEXEME_MODIFIER, self::IN_DECIMAL_POINT,   self::ST_MANTISSA),
                               array(self::ST_LEXEME_MODIFIER, self::IN_ASCII_DIGIT,     self::ST_NUMBER),

                               // IN_CHAR              not allowed
                               array(self::ST_LEXEME_MODIFIER, self::IN_CHAR,            self::ST_ERROR, $lexemeModifierErrorAction),
                             ));
        $this->addRules(array( array(self::ST_NUMBER, self::IN_WHITE_SPACE,     self::ST_WHITE_SPACE),
                               array(self::ST_NUMBER, self::IN_SYNT_CHAR,       self::ST_SYNT_LEXEME),
                               array(self::ST_NUMBER, self::IN_MUTABLE_CHAR,    self::ST_SYNT_LEXEME),
                               array(self::ST_NUMBER, self::IN_LEXEME_MODIFIER, self::ST_LEXEME_MODIFIER),

                               // IN_ESCAPE_CHAR       not allowed
                               array(self::ST_NUMBER, self::IN_ESCAPE_CHAR,     self::ST_ERROR, $wrongNumberErrorAction),

                               // IN_QUOTE             not allowed
                               array(self::ST_NUMBER, self::IN_QUOTE,           self::ST_ERROR, $wrongNumberErrorAction),

                               array(self::ST_NUMBER, self::IN_DECIMAL_POINT,   self::ST_MANTISSA),
                               array(self::ST_NUMBER, self::IN_ASCII_DIGIT,     self::ST_NUMBER),

                               // IN_CHAR              not allowed
                               array(self::ST_NUMBER, self::IN_CHAR,            self::ST_ERROR, $wrongNumberErrorAction),
                             ));
        $this->addRules(array( array(self::ST_MANTISSA, self::IN_WHITE_SPACE,     self::ST_WHITE_SPACE),
                               array(self::ST_MANTISSA, self::IN_SYNT_CHAR,       self::ST_SYNT_LEXEME),
                               array(self::ST_MANTISSA, self::IN_MUTABLE_CHAR,    self::ST_SYNT_LEXEME),
                               array(self::ST_MANTISSA, self::IN_LEXEME_MODIFIER, self::ST_LEXEME_MODIFIER),

                               // IN_ESCAPE_CHAR       not allowed
                               array(self::ST_MANTISSA, self::IN_ESCAPE_CHAR,     self::ST_ERROR, $wrongNumberErrorAction),

                               // IN_QUOTE             not allowed
                               array(self::ST_MANTISSA, self::IN_QUOTE,           self::ST_ERROR, $wrongNumberErrorAction),

                               // IN_DECIMAL_POINT     not allowed
                               array(self::ST_MANTISSA, self::IN_DECIMAL_POINT,   self::ST_ERROR, $wrongNumberErrorAction),

                               array(self::ST_MANTISSA, self::IN_ASCII_DIGIT,     self::ST_MANTISSA),

                               // IN_CHAR              not allowed
                               array(self::ST_MANTISSA, self::IN_CHAR,            self::ST_ERROR, $wrongNumberErrorAction),
                             ));


        /** Actions */
        $syntaxLexemeAction    = new Lucene\FSMAction($this, 'addQuerySyntaxLexeme');
        $lexemeModifierAction  = new Lucene\FSMAction($this, 'addLexemeModifier');
        $addLexemeAction       = new Lucene\FSMAction($this, 'addLexeme');
        $addQuotedLexemeAction = new Lucene\FSMAction($this, 'addQuotedLexeme');
        $addNumberLexemeAction = new Lucene\FSMAction($this, 'addNumberLexeme');
        $addLexemeCharAction   = new Lucene\FSMAction($this, 'addLexemeChar');


        /** Syntax lexeme */
        $this->addEntryAction(self::ST_SYNT_LEXEME,  $syntaxLexemeAction);
        // Two lexemes in succession
        $this->addTransitionAction(self::ST_SYNT_LEXEME, self::ST_SYNT_LEXEME, $syntaxLexemeAction);


        /** Lexeme */
        $this->addEntryAction(self::ST_LEXEME,                       $addLexemeCharAction);
        $this->addTransitionAction(self::ST_LEXEME, self::ST_LEXEME, $addLexemeCharAction);
        // ST_ESCAPED_CHAR => ST_LEXEME transition is covered by ST_LEXEME entry action

        $this->addTransitionAction(self::ST_LEXEME, self::ST_WHITE_SPACE,     $addLexemeAction);
        $this->addTransitionAction(self::ST_LEXEME, self::ST_SYNT_LEXEME,     $addLexemeAction);
        $this->addTransitionAction(self::ST_LEXEME, self::ST_QUOTED_LEXEME,   $addLexemeAction);
        $this->addTransitionAction(self::ST_LEXEME, self::ST_LEXEME_MODIFIER, $addLexemeAction);
        $this->addTransitionAction(self::ST_LEXEME, self::ST_NUMBER,          $addLexemeAction);
        $this->addTransitionAction(self::ST_LEXEME, self::ST_MANTISSA,        $addLexemeAction);


        /** Quoted lexeme */
        // We don't need entry action (skeep quote)
        $this->addTransitionAction(self::ST_QUOTED_LEXEME, self::ST_QUOTED_LEXEME, $addLexemeCharAction);
        $this->addTransitionAction(self::ST_ESCAPED_QCHAR, self::ST_QUOTED_LEXEME, $addLexemeCharAction);
        // Closing quote changes state to the ST_WHITE_SPACE   other states are not used
        $this->addTransitionAction(self::ST_QUOTED_LEXEME, self::ST_WHITE_SPACE,   $addQuotedLexemeAction);


        /** Lexeme modifier */
        $this->addEntryAction(self::ST_LEXEME_MODIFIER, $lexemeModifierAction);


        /** Number */
        $this->addEntryAction(self::ST_NUMBER,                           $addLexemeCharAction);
        $this->addEntryAction(self::ST_MANTISSA,                         $addLexemeCharAction);
        $this->addTransitionAction(self::ST_NUMBER,   self::ST_NUMBER,   $addLexemeCharAction);
        // ST_NUMBER => ST_MANTISSA transition is covered by ST_MANTISSA entry action
        $this->addTransitionAction(self::ST_MANTISSA, self::ST_MANTISSA, $addLexemeCharAction);

        $this->addTransitionAction(self::ST_NUMBER,   self::ST_WHITE_SPACE,     $addNumberLexemeAction);
        $this->addTransitionAction(self::ST_NUMBER,   self::ST_SYNT_LEXEME,     $addNumberLexemeAction);
        $this->addTransitionAction(self::ST_NUMBER,   self::ST_LEXEME_MODIFIER, $addNumberLexemeAction);
        $this->addTransitionAction(self::ST_MANTISSA, self::ST_WHITE_SPACE,     $addNumberLexemeAction);
        $this->addTransitionAction(self::ST_MANTISSA, self::ST_SYNT_LEXEME,     $addNumberLexemeAction);
        $this->addTransitionAction(self::ST_MANTISSA, self::ST_LEXEME_MODIFIER, $addNumberLexemeAction);
    }




    /**
     * Translate input char to an input symbol of state machine
     *
     * @param string $char
     * @return integer
     */
    private function _translateInput($char)
    {
        if        (strpos(self::QUERY_WHITE_SPACE_CHARS,    $char) !== false) { return self::IN_WHITE_SPACE;
        } elseif (strpos(self::QUERY_SYNT_CHARS,           $char) !== false) { return self::IN_SYNT_CHAR;
        } elseif (strpos(self::QUERY_MUTABLE_CHARS,        $char) !== false) { return self::IN_MUTABLE_CHAR;
        } elseif (strpos(self::QUERY_LEXEMEMODIFIER_CHARS, $char) !== false) { return self::IN_LEXEME_MODIFIER;
        } elseif (strpos(self::QUERY_ASCIIDIGITS_CHARS,    $char) !== false) { return self::IN_ASCII_DIGIT;
        } elseif ($char === '"' )                                            { return self::IN_QUOTE;
        } elseif ($char === '.' )                                            { return self::IN_DECIMAL_POINT;
        } elseif ($char === '\\')                                            { return self::IN_ESCAPE_CHAR;
        } else                                                                { return self::IN_CHAR;
        }
    }


    /**
     * This method is used to tokenize query string into lexemes
     *
     * @param string $inputString
     * @param string $encoding
     * @return array
     * @throws \ZendSearch\Lucene\Search\Exception\QueryParserException
     */
    public function tokenize($inputString, $encoding)
    {
        $this->reset();

        $this->_lexemes     = array();
        $this->_queryString = array();

        if (PHP_OS == 'AIX' && $encoding == '') {
            $encoding = 'ISO8859-1';
        }
        $strLength = iconv_strlen($inputString, $encoding);

        // Workaround for iconv_substr bug
        $inputString .= ' ';

        for ($count = 0; $count < $strLength; $count++) {
            $this->_queryString[$count] = iconv_substr($inputString, $count, 1, $encoding);
        }

        for ($this->_queryStringPosition = 0;
             $this->_queryStringPosition < count($this->_queryString);
             $this->_queryStringPosition++) {
            $this->process($this->_translateInput($this->_queryString[$this->_queryStringPosition]));
        }

        $this->process(self::IN_WHITE_SPACE);

        if ($this->getState() != self::ST_WHITE_SPACE) {
            throw new QueryParserException('Unexpected end of query');
        }

        $this->_queryString = null;

        return $this->_lexemes;
    }



    /*********************************************************************
     * Actions implementation
     *
     * Actions affect on recognized lexemes list
     *********************************************************************/

    /**
     * Add query syntax lexeme
     *
     * @throws \ZendSearch\Lucene\Search\Exception\QueryParserException
     */
    public function addQuerySyntaxLexeme()
    {
        $lexeme = $this->_queryString[$this->_queryStringPosition];

        // Process two char lexemes
        if (strpos(self::QUERY_DOUBLECHARLEXEME_CHARS, $lexeme) !== false) {
            // increase current position in a query string
            $this->_queryStringPosition++;

            // check,
            if ($this->_queryStringPosition == count($this->_queryString)  ||
                $this->_queryString[$this->_queryStringPosition] != $lexeme) {
                    throw new QueryParserException('Two chars lexeme expected. ' . $this->_positionMsg());
                }

            // duplicate character
            $lexeme .= $lexeme;
        }

        $token = new QueryToken(QueryToken::TC_SYNTAX_ELEMENT,
                                $lexeme,
                                $this->_queryStringPosition);

        // Skip this lexeme if it's a field indicator ':' and treat previous as 'field' instead of 'word'
        if ($token->type == QueryToken::TT_FIELD_INDICATOR) {
            $token = array_pop($this->_lexemes);
            if ($token === null  ||  $token->type != QueryToken::TT_WORD) {
                throw new QueryParserException('Field mark \':\' must follow field name. ' . $this->_positionMsg());
            }

            $token->type = QueryToken::TT_FIELD;
        }

        $this->_lexemes[] = $token;
    }

    /**
     * Add lexeme modifier
     */
    public function addLexemeModifier()
    {
        $this->_lexemes[] = new QueryToken(QueryToken::TC_SYNTAX_ELEMENT,
                                           $this->_queryString[$this->_queryStringPosition],
                                           $this->_queryStringPosition);
    }


    /**
     * Add lexeme
     */
    public function addLexeme()
    {
        $this->_lexemes[] = new QueryToken(QueryToken::TC_WORD,
                                           $this->_currentLexeme,
                                           $this->_queryStringPosition - 1);

        $this->_currentLexeme = '';
    }

    /**
     * Add quoted lexeme
     */
    public function addQuotedLexeme()
    {
        $this->_lexemes[] = new QueryToken(QueryToken::TC_PHRASE,
                                           $this->_currentLexeme,
                                           $this->_queryStringPosition);

        $this->_currentLexeme = '';
    }

    /**
     * Add number lexeme
     */
    public function addNumberLexeme()
    {
        $this->_lexemes[] = new QueryToken(QueryToken::TC_NUMBER,
                                           $this->_currentLexeme,
                                           $this->_queryStringPosition - 1);
        $this->_currentLexeme = '';
    }

    /**
     * Extend lexeme by one char
     */
    public function addLexemeChar()
    {
        $this->_currentLexeme .= $this->_queryString[$this->_queryStringPosition];
    }


    /**
     * Position message
     *
     * @return string
     */
    private function _positionMsg()
    {
        return 'Position is ' . $this->_queryStringPosition . '.';
    }


    /*********************************************************************
     * Syntax errors actions
     *********************************************************************/
    public function lexModifierErrException()
    {
        throw new QueryParserException('Lexeme modifier character can be followed only by number, white space or query syntax element. ' . $this->_positionMsg());
    }
    public function quoteWithinLexemeErrException()
    {
        throw new QueryParserException('Quote within lexeme must be escaped by \'\\\' char. ' . $this->_positionMsg());
    }
    public function wrongNumberErrException()
    {
        throw new QueryParserException('Wrong number syntax.' . $this->_positionMsg());
    }
}

