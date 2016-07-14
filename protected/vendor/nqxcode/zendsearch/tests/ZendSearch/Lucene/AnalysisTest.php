<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearchTest\Lucene;

use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Analysis\Analyzer\AnalyzerInterface;
use ZendSearch\Lucene\Analysis\Analyzer\Common;
use ZendSearch\Lucene\Analysis\Analyzer\Common\Text;
use ZendSearch\Lucene\Analysis\Analyzer\Common\TextNum;
use ZendSearch\Lucene\Analysis\Analyzer\Common\Utf8;
use ZendSearch\Lucene\Analysis\Analyzer\Common\Utf8Num;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage UnitTests
 * @group      Zend_Search_Lucene
 */
class AnalysisTest extends \PHPUnit_Framework_TestCase
{
    public function testAnalyzer()
    {
        $currentAnalyzer = Analyzer::getDefault();
        $this->assertTrue($currentAnalyzer instanceof AnalyzerInterface);

        /** Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num */

        $newAnalyzer = new Common\Utf8Num();
        Analyzer::setDefault($newAnalyzer);
        $this->assertTrue(Analyzer::getDefault() === $newAnalyzer);

        // Set analyzer to the default value (used in other tests)
        Analyzer::setDefault($currentAnalyzer);
    }

    public function testText()
    {
        /** Zend_Search_Lucene_Analysis_Analyzer_Common_Text */

        $analyzer = new Common\Text();

        $tokenList = $analyzer->tokenize('Word1 Word2 anotherWord');

        $this->assertEquals(count($tokenList), 3);

        $this->assertEquals($tokenList[0]->getTermText(),         'Word');
        $this->assertEquals($tokenList[0]->getStartOffset(),       0);
        $this->assertEquals($tokenList[0]->getEndOffset(),         4);
        $this->assertEquals($tokenList[0]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[1]->getTermText(),         'Word');
        $this->assertEquals($tokenList[1]->getStartOffset(),       6);
        $this->assertEquals($tokenList[1]->getEndOffset(),         10);
        $this->assertEquals($tokenList[1]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[2]->getTermText(),         'anotherWord');
        $this->assertEquals($tokenList[2]->getStartOffset(),       12);
        $this->assertEquals($tokenList[2]->getEndOffset(),         23);
        $this->assertEquals($tokenList[2]->getPositionIncrement(), 1);
    }

    public function testTextCaseInsensitive()
    {
        /** Zend_Search_Lucene_Analysis_Analyzer_Common_Text_CaseInsensitive */

        $analyzer = new Text\CaseInsensitive();

        $tokenList = $analyzer->tokenize('Word1 Word2 anotherWord');

        $this->assertEquals(count($tokenList), 3);

        $this->assertEquals($tokenList[0]->getTermText(),         'word');
        $this->assertEquals($tokenList[0]->getStartOffset(),       0);
        $this->assertEquals($tokenList[0]->getEndOffset(),         4);
        $this->assertEquals($tokenList[0]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[1]->getTermText(),         'word');
        $this->assertEquals($tokenList[1]->getStartOffset(),       6);
        $this->assertEquals($tokenList[1]->getEndOffset(),         10);
        $this->assertEquals($tokenList[1]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[2]->getTermText(),         'anotherword');
        $this->assertEquals($tokenList[2]->getStartOffset(),       12);
        $this->assertEquals($tokenList[2]->getEndOffset(),         23);
        $this->assertEquals($tokenList[2]->getPositionIncrement(), 1);
    }

    public function testTextNum()
    {
        /** Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum */

        $analyzer = new Common\TextNum();

        $tokenList = $analyzer->tokenize('Word1 Word2 anotherWord');

        $this->assertEquals(count($tokenList), 3);

        $this->assertEquals($tokenList[0]->getTermText(),         'Word1');
        $this->assertEquals($tokenList[0]->getStartOffset(),       0);
        $this->assertEquals($tokenList[0]->getEndOffset(),         5);
        $this->assertEquals($tokenList[0]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[1]->getTermText(),         'Word2');
        $this->assertEquals($tokenList[1]->getStartOffset(),       6);
        $this->assertEquals($tokenList[1]->getEndOffset(),         11);
        $this->assertEquals($tokenList[1]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[2]->getTermText(),         'anotherWord');
        $this->assertEquals($tokenList[2]->getStartOffset(),       12);
        $this->assertEquals($tokenList[2]->getEndOffset(),         23);
        $this->assertEquals($tokenList[2]->getPositionIncrement(), 1);
    }

    public function testTextNumCaseInsensitive()
    {
        /** Zend_Search_Lucene_Analysis_Analyzer_Common_TextNum_CaseInsensitive */

        $analyzer = new TextNum\CaseInsensitive();

        $tokenList = $analyzer->tokenize('Word1 Word2 anotherWord');

        $this->assertEquals(count($tokenList), 3);

        $this->assertEquals($tokenList[0]->getTermText(),         'word1');
        $this->assertEquals($tokenList[0]->getStartOffset(),       0);
        $this->assertEquals($tokenList[0]->getEndOffset(),         5);
        $this->assertEquals($tokenList[0]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[1]->getTermText(),         'word2');
        $this->assertEquals($tokenList[1]->getStartOffset(),       6);
        $this->assertEquals($tokenList[1]->getEndOffset(),         11);
        $this->assertEquals($tokenList[1]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[2]->getTermText(),         'anotherword');
        $this->assertEquals($tokenList[2]->getStartOffset(),       12);
        $this->assertEquals($tokenList[2]->getEndOffset(),         23);
        $this->assertEquals($tokenList[2]->getPositionIncrement(), 1);
    }

    public function testUtf8()
    {
        if (@preg_match('/\pL/u', 'a') != 1) {
            // PCRE unicode support is turned off
            return;
        }

        /** Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8 */

        $analyzer = new Common\Utf8();

        // UTF-8 text with a cyrillic symbols
        $tokenList = $analyzer->tokenize('Слово1 Слово2 ДругоеСлово', 'UTF-8');

        $this->assertEquals(count($tokenList), 3);

        $this->assertEquals($tokenList[0]->getTermText(),         'Слово');
        $this->assertEquals($tokenList[0]->getStartOffset(),       0);
        $this->assertEquals($tokenList[0]->getEndOffset(),         5);
        $this->assertEquals($tokenList[0]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[1]->getTermText(),         'Слово');
        $this->assertEquals($tokenList[1]->getStartOffset(),       7);
        $this->assertEquals($tokenList[1]->getEndOffset(),         12);
        $this->assertEquals($tokenList[1]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[2]->getTermText(),         'ДругоеСлово');
        $this->assertEquals($tokenList[2]->getStartOffset(),       14);
        $this->assertEquals($tokenList[2]->getEndOffset(),         25);
        $this->assertEquals($tokenList[2]->getPositionIncrement(), 1);
    }

    public function testUtf8Num()
    {
        if (@preg_match('/\pL/u', 'a') != 1) {
            // PCRE unicode support is turned off
            return;
        }

        /** Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num */

        $analyzer = new Common\Utf8Num();

        // UTF-8 text with a cyrillic symbols
        $tokenList = $analyzer->tokenize('Слово1 Слово2 ДругоеСлово', 'UTF-8');

        $this->assertEquals(count($tokenList), 3);

        $this->assertEquals($tokenList[0]->getTermText(),         'Слово1');
        $this->assertEquals($tokenList[0]->getStartOffset(),       0);
        $this->assertEquals($tokenList[0]->getEndOffset(),         6);
        $this->assertEquals($tokenList[0]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[1]->getTermText(),         'Слово2');
        $this->assertEquals($tokenList[1]->getStartOffset(),       7);
        $this->assertEquals($tokenList[1]->getEndOffset(),         13);
        $this->assertEquals($tokenList[1]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[2]->getTermText(),         'ДругоеСлово');
        $this->assertEquals($tokenList[2]->getStartOffset(),       14);
        $this->assertEquals($tokenList[2]->getEndOffset(),         25);
        $this->assertEquals($tokenList[2]->getPositionIncrement(), 1);
    }

    public function testUtf8CaseInsensitive()
    {
        if (@preg_match('/\pL/u', 'a') != 1) {
            // PCRE unicode support is turned off
            return;
        }
        if (!function_exists('mb_strtolower')) {
            // mbstring extension is disabled
            return;
        }

        /** Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8_CaseInsensitive */

        $analyzer = new Utf8\CaseInsensitive();

        // UTF-8 text with a cyrillic symbols
        $tokenList = $analyzer->tokenize('Слово1 Слово2 ДругоеСлово', 'UTF-8');

        $this->assertEquals(count($tokenList), 3);

        $this->assertEquals($tokenList[0]->getTermText(),         'слово');
        $this->assertEquals($tokenList[0]->getStartOffset(),       0);
        $this->assertEquals($tokenList[0]->getEndOffset(),         5);
        $this->assertEquals($tokenList[0]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[1]->getTermText(),         'слово');
        $this->assertEquals($tokenList[1]->getStartOffset(),       7);
        $this->assertEquals($tokenList[1]->getEndOffset(),         12);
        $this->assertEquals($tokenList[1]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[2]->getTermText(),         'другоеслово');
        $this->assertEquals($tokenList[2]->getStartOffset(),       14);
        $this->assertEquals($tokenList[2]->getEndOffset(),         25);
        $this->assertEquals($tokenList[2]->getPositionIncrement(), 1);
    }

    public function testUtf8NumCaseInsensitive()
    {
        if (@preg_match('/\pL/u', 'a') != 1) {
            // PCRE unicode support is turned off
            return;
        }
        if (!function_exists('mb_strtolower')) {
            // mbstring extension is disabled
            return;
        }

        /** Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive */
        $analyzer = new Utf8Num\CaseInsensitive();

        // UTF-8 text with a cyrillic symbols
        $tokenList = $analyzer->tokenize('Слово1 Слово2 ДругоеСлово', 'UTF-8');

        $this->assertEquals(count($tokenList), 3);

        $this->assertEquals($tokenList[0]->getTermText(),         'слово1');
        $this->assertEquals($tokenList[0]->getStartOffset(),       0);
        $this->assertEquals($tokenList[0]->getEndOffset(),         6);
        $this->assertEquals($tokenList[0]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[1]->getTermText(),         'слово2');
        $this->assertEquals($tokenList[1]->getStartOffset(),       7);
        $this->assertEquals($tokenList[1]->getEndOffset(),         13);
        $this->assertEquals($tokenList[1]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[2]->getTermText(),         'другоеслово');
        $this->assertEquals($tokenList[2]->getStartOffset(),       14);
        $this->assertEquals($tokenList[2]->getEndOffset(),         25);
        $this->assertEquals($tokenList[2]->getPositionIncrement(), 1);
    }

    public function testEncoding()
    {
        if (PHP_OS == 'AIX') {
            $this->markTestSkipped('Test not available on AIX');
        }

        /** Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8 */

        $analyzer = new Common\Utf8();

        // UTF-8 text with a cyrillic symbols
        $tokenList = $analyzer->tokenize(iconv('UTF-8', 'Windows-1251', 'Слово1 Слово2 ДругоеСлово'), 'Windows-1251');

        $this->assertEquals(count($tokenList), 3);

        $this->assertEquals($tokenList[0]->getTermText(),         'Слово');
        $this->assertEquals($tokenList[0]->getStartOffset(),       0);
        $this->assertEquals($tokenList[0]->getEndOffset(),         5);
        $this->assertEquals($tokenList[0]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[1]->getTermText(),         'Слово');
        $this->assertEquals($tokenList[1]->getStartOffset(),       7);
        $this->assertEquals($tokenList[1]->getEndOffset(),         12);
        $this->assertEquals($tokenList[1]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[2]->getTermText(),         'ДругоеСлово');
        $this->assertEquals($tokenList[2]->getStartOffset(),       14);
        $this->assertEquals($tokenList[2]->getEndOffset(),         25);
        $this->assertEquals($tokenList[2]->getPositionIncrement(), 1);
    }

    public function testStopWords()
    {
        /** Zend_Search_Lucene_Analysis_Analyzer_Common_Text_CaseInsensitive */

        /** Zend_Search_Lucene_Analysis_TokenFilter_StopWords */

        $analyzer = new Text\CaseInsensitive();
        $stopWordsFilter = new \ZendSearch\Lucene\Analysis\TokenFilter\StopWords(array('word', 'and', 'or'));

        $analyzer->addFilter($stopWordsFilter);

        $tokenList = $analyzer->tokenize('Word1 Word2 anotherWord');

        $this->assertEquals(count($tokenList), 1);

        $this->assertEquals($tokenList[0]->getTermText(),         'anotherword');
        $this->assertEquals($tokenList[0]->getStartOffset(),       12);
        $this->assertEquals($tokenList[0]->getEndOffset(),         23);
        $this->assertEquals($tokenList[0]->getPositionIncrement(), 1);
    }

    public function testShortWords()
    {
        /** Zend_Search_Lucene_Analysis_Analyzer_Common_Text_CaseInsensitive */

        /** Zend_Search_Lucene_Analysis_TokenFilter_ShortWords */

        $analyzer = new Text\CaseInsensitive();
        $stopWordsFilter = new \ZendSearch\Lucene\Analysis\TokenFilter\ShortWords(4 /* Minimal length */);

        $analyzer->addFilter($stopWordsFilter);

        $tokenList = $analyzer->tokenize('Word1 and anotherWord');

        $this->assertEquals(count($tokenList), 2);

        $this->assertEquals($tokenList[0]->getTermText(),         'word');
        $this->assertEquals($tokenList[0]->getStartOffset(),       0);
        $this->assertEquals($tokenList[0]->getEndOffset(),         4);
        $this->assertEquals($tokenList[0]->getPositionIncrement(), 1);

        $this->assertEquals($tokenList[1]->getTermText(),         'anotherword');
        $this->assertEquals($tokenList[1]->getStartOffset(),       10);
        $this->assertEquals($tokenList[1]->getEndOffset(),         21);
        $this->assertEquals($tokenList[1]->getPositionIncrement(), 1);
    }
}
