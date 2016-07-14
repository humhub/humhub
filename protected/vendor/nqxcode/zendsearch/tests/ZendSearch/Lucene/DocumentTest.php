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

use ZendSearch\Lucene\Document;
use ZendSearch\Lucene;

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage UnitTests
 * @group      Zend_Search_Lucene
 */
class DocumentTest extends \PHPUnit_Framework_TestCase
{

    private function _clearDirectory($dirName)
    {
        if (!file_exists($dirName) || !is_dir($dirName))  {
            return;
        }

        // remove files from temporary directory
        $dir = opendir($dirName);
        while (($file = readdir($dir)) !== false) {
            if (!is_dir($dirName . '/' . $file)) {
                @unlink($dirName . '/' . $file);
            }
        }
        closedir($dir);
    }

    public function testCreate()
    {
        $document =  new Document();

        $this->assertEquals($document->boost, 1);
    }

    public function testFields()
    {
        $document =  new Document();

        $document->addField(Document\Field::Text('title',      'Title'));
        $document->addField(Document\Field::Text('annotation', 'Annotation'));
        $document->addField(Document\Field::Text('body',       'Document body, document body, document body...'));

        $fieldnamesDiffArray = array_diff($document->getFieldNames(), array('title', 'annotation', 'body'));
        $this->assertTrue(is_array($fieldnamesDiffArray));
        $this->assertEquals(count($fieldnamesDiffArray), 0);

        $this->assertEquals($document->title,      'Title');
        $this->assertEquals($document->annotation, 'Annotation');
        $this->assertEquals($document->body,       'Document body, document body, document body...');

        $this->assertEquals($document->getField('title')->value,      'Title');
        $this->assertEquals($document->getField('annotation')->value, 'Annotation');
        $this->assertEquals($document->getField('body')->value,       'Document body, document body, document body...');

        $this->assertEquals($document->getFieldValue('title'),      'Title');
        $this->assertEquals($document->getFieldValue('annotation'), 'Annotation');
        $this->assertEquals($document->getFieldValue('body'),       'Document body, document body, document body...');


        if (PHP_OS == 'AIX') {
            return; // tests below here not valid on AIX
        }

        $wordsWithUmlautsIso88591 = iconv('UTF-8', 'ISO-8859-1', 'Words with umlauts: åãü...');
        $document->addField(Document\Field::Text('description', $wordsWithUmlautsIso88591, 'ISO-8859-1'));
        $this->assertEquals($document->description, $wordsWithUmlautsIso88591);
        $this->assertEquals($document->getFieldUtf8Value('description'), 'Words with umlauts: åãü...');
    }

    public function testAddFieldMethodChaining()
    {
        $document =  new Document();
        $this->assertTrue($document->addField(Document\Field::Text('title', 'Title')) instanceof Document);

        $document =  new Document();
        $document->addField(Document\Field::Text('title',      'Title'))
                 ->addField(Document\Field::Text('annotation', 'Annotation'))
                 ->addField(Document\Field::Text('body',       'Document body, document body, document body...'));
    }


    public function testHtmlHighlighting()
    {
        $doc = Document\HTML::loadHTML('<HTML><HEAD><TITLE>Page title</TITLE></HEAD><BODY>Document body.</BODY></HTML>');
        $this->assertTrue($doc instanceof Document\HTML);

        $doc->highlight('document', '#66ffff');
        $this->assertTrue(strpos($doc->getHTML(), '<b style="color:black;background-color:#66ffff">Document</b> body.') !== false);
    }

    public function testHtmlExtendedHighlighting()
    {
        $doc = Document\HTML::loadHTML('<HTML><HEAD><TITLE>Page title</TITLE></HEAD><BODY>Document body.</BODY></HTML>');
        $this->assertTrue($doc instanceof Document\HTML);

        $doc->highlightExtended('document',
                                array('\ZendSearchTest\Lucene\DocHighlightingContainer',
                                      'extendedHighlightingCallback'),
                                array('style="color:black;background-color:#ff66ff"',
                                      '(!!!)'));
        $this->assertTrue(strpos($doc->getHTML(), '<b style="color:black;background-color:#ff66ff">Document</b>(!!!) body.') !== false);
    }

    public function testHtmlWordsHighlighting()
    {
        $doc = Document\HTML::loadHTML('<HTML><HEAD><TITLE>Page title</TITLE></HEAD><BODY>Document body.</BODY></HTML>');
        $this->assertTrue($doc instanceof Document\HTML);

        $doc->highlight(array('document', 'body'), '#66ffff');
        $highlightedHTML = $doc->getHTML();
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">Document</b>') !== false);
        $this->assertTrue(strpos($highlightedHTML, '<b style="color:black;background-color:#66ffff">body</b>')     !== false);
    }

    public function testHtmlExtendedHighlightingCorrectWrongHtml()
    {
        $doc = Document\HTML::loadHTML('<HTML><HEAD><TITLE>Page title</TITLE></HEAD><BODY>Document body.</BODY></HTML>');
        $this->assertTrue($doc instanceof Document\HTML);

        $doc->highlightExtended('document',
                                array('\ZendSearchTest\Lucene\DocHighlightingContainer',
                                      'extendedHighlightingCallback'),
                                array('style="color:black;background-color:#ff66ff"',
                                      '<h3>(!!!)' /* Wrong HTML here, <h3> tag is not closed */));
        $this->assertTrue(strpos($doc->getHTML(), '<b style="color:black;background-color:#ff66ff">Document</b><h3>(!!!)</h3> body.') !== false);
    }

    public function testHtmlLinksProcessing()
    {
        $doc =  Document\HTML::loadHTMLFile(__DIR__ . '/_indexSource/_files/contributing.documentation.html', true);
        $this->assertTrue($doc instanceof Document\HTML);

        $this->assertTrue(array_values($doc->getHeaderLinks()) ==
                          array('index.html', 'contributing.html', 'contributing.bugs.html', 'contributing.wishlist.html'));
        $this->assertTrue(array_values($doc->getLinks()) ==
                          array('contributing.bugs.html',
                                'contributing.wishlist.html',
                                'developers.documentation.html',
                                'faq.translators-revision-tracking.html',
                                'index.html',
                                'contributing.html'));
    }


    /**
     * @group ZF-4252
     */
    public function testHtmlInlineTagsIndexing()
    {
        $index = Lucene\Lucene::create(__DIR__ . '/_index/_files');

        $htmlString = '<html><head><title>Hello World</title></head>'
                    . '<body><b>Zend</b>Framework' . "\n" . ' <div>Foo</div>Bar ' . "\n"
                    . ' <strong>Test</strong></body></html>';

        $doc = Document\Html::loadHTML($htmlString);

        $index->addDocument($doc);

        $hits = $index->find('FooBar');
        $this->assertEquals(count($hits), 0);

        $hits = $index->find('ZendFramework');
        $this->assertEquals(count($hits), 1);

        unset($index);
        $this->_clearDirectory(__DIR__ . '/_index/_files');
    }

    /**
     * @group ZF-8740
     */
    public function testHtmlAreaTags()
    {
        $html = '<HTML>'
                . '<HEAD><TITLE>Page title</TITLE></HEAD>'
                . '<BODY>'
                .   'Document body.'
                .   '<img src="img.png" width="640" height="480" alt="some image" usemap="#some_map" />'
                .   '<map name="some_map">'
                .     '<area shape="rect" coords="0,0,100,100" href="link3.html" alt="Link 3" />'
                .     '<area shape="rect" coords="200,200,300,300" href="link4.html" alt="Link 4" />'
                .   '</map>'
                .   '<a href="link1.html">Link 1</a>.'
                .   '<a href="link2.html" rel="nofollow">Link 1</a>.'
                . '</BODY>'
              . '</HTML>';

        $oldNoFollowValue = Document\Html::getExcludeNoFollowLinks();

        Document\Html::setExcludeNoFollowLinks(false);
        $doc1 = Document\Html::loadHTML($html);
        $this->assertTrue($doc1 instanceof Document\Html);
        $links = array('link1.html', 'link2.html', 'link3.html', 'link4.html');
        $this->assertTrue(array_values($doc1->getLinks()) == $links);
    }

    public function testHtmlNoFollowLinks()
    {
        $html = '<HTML>'
                . '<HEAD><TITLE>Page title</TITLE></HEAD>'
                . '<BODY>'
                .   'Document body.'
                .   '<a href="link1.html">Link 1</a>.'
                .   '<a href="link2.html" rel="nofollow">Link 1</a>.'
                . '</BODY>'
              . '</HTML>';

        $oldNoFollowValue = Document\HTML::getExcludeNoFollowLinks();

        Document\HTML::setExcludeNoFollowLinks(false);
        $doc1 = Document\HTML::loadHTML($html);
        $this->assertTrue($doc1 instanceof Document\HTML);
        $this->assertTrue(array_values($doc1->getLinks()) == array('link1.html', 'link2.html'));

        Document\HTML::setExcludeNoFollowLinks(true);
        $doc2 = Document\HTML::loadHTML($html);
        $this->assertTrue($doc2 instanceof Document\HTML);
        $this->assertTrue(array_values($doc2->getLinks()) == array('link1.html'));
    }

    public function testDocx()
    {
        if (!class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive class (Zip extension) is not loaded');
        }

        $docxDocument = Document\Docx::loadDocxFile(__DIR__ . '/_openXmlDocuments/test.docx', true);

        $this->assertTrue($docxDocument instanceof Document\Docx);
        $this->assertEquals($docxDocument->getFieldValue('title'), 'Test document');
        $this->assertEquals($docxDocument->getFieldValue('description'), 'This is a test document which can be used to demonstrate something.');
        $this->assertTrue($docxDocument->getFieldValue('body') != '');

        try {
            $docxDocument1 = Document\Docx::loadDocxFile(__DIR__ . '/_openXmlDocuments/dummy.docx', true);

            $this->fail('File not readable exception is expected.');
        } catch (Document\Exception\InvalidArgumentException $e) {
            if (strpos($e->getMessage(), 'is not readable') === false) {
                // Passthrough exception
                throw $e;
            }
        }
    }

    public function testPptx()
    {
        if (!class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive class (Zip extension) is not loaded');
        }

        $pptxDocument = Document\Pptx::loadPptxFile(__DIR__ . '/_openXmlDocuments/test.pptx', true);

        $this->assertTrue($pptxDocument instanceof Document\Pptx);
        $this->assertEquals($pptxDocument->getFieldValue('title'), 'Test document');
        $this->assertEquals($pptxDocument->getFieldValue('description'), 'This is a test document which can be used to demonstrate something.');
        $this->assertTrue($pptxDocument->getFieldValue('body') != '');
    }

    public function testXlsx()
    {
        if (!class_exists('ZipArchive')) {
            $this->markTestSkipped('ZipArchive class (Zip extension) is not loaded');
        }

        $xlsxDocument = Document\Xlsx::loadXlsxFile(__DIR__ . '/_openXmlDocuments/test.xlsx', true);

        $this->assertTrue($xlsxDocument instanceof Document\Xlsx);
        $this->assertEquals($xlsxDocument->getFieldValue('title'), 'Test document');
        $this->assertEquals($xlsxDocument->getFieldValue('description'), 'This is a test document which can be used to demonstrate something.');
        $this->assertTrue($xlsxDocument->getFieldValue('body') != '');
        $this->assertTrue( strpos($xlsxDocument->getFieldValue('body'), 'ipsum') !== false );
    }
}


class DocHighlightingContainer
{
    public static function extendedHighlightingCallback($stringToHighlight, $param1, $param2)
    {
        return '<b ' . $param1 . '>' . $stringToHighlight . '</b>' . $param2;
    }
}
