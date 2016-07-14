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

/**
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage UnitTests
 * @group      Zend_Search_Lucene
 */
class FieldTest extends \PHPUnit_Framework_TestCase
{
    public function testBinary()
    {
        $field = Document\Field::Binary('field', 'value');

        $this->assertEquals($field->boost, 1);
        $this->assertEquals($field->encoding, '');
        $this->assertEquals($field->isBinary,    true);
        $this->assertEquals($field->isIndexed,   false);
        $this->assertEquals($field->isStored,    true);
        $this->assertEquals($field->isTokenized, false);

        $this->assertEquals($field->name, 'field');
        $this->assertEquals($field->value, 'value');
    }

    public function testKeyword()
    {
        $field = Document\Field::Keyword('field', 'value');

        $this->assertEquals($field->boost, 1);
        $this->assertEquals($field->encoding, 'UTF-8');
        $this->assertEquals($field->isBinary,    false);
        $this->assertEquals($field->isIndexed,   true);
        $this->assertEquals($field->isStored,    true);
        $this->assertEquals($field->isTokenized, false);

        $this->assertEquals($field->name, 'field');
        $this->assertEquals($field->value, 'value');
    }

    public function testText()
    {
        $field = Document\Field::Text('field', 'value');

        $this->assertEquals($field->boost, 1);
        $this->assertEquals($field->encoding, 'UTF-8');
        $this->assertEquals($field->isBinary,    false);
        $this->assertEquals($field->isIndexed,   true);
        $this->assertEquals($field->isStored,    true);
        $this->assertEquals($field->isTokenized, true);

        $this->assertEquals($field->name, 'field');
        $this->assertEquals($field->value, 'value');
    }

    public function testUnIndexed()
    {
        $field = Document\Field::UnIndexed('field', 'value');

        $this->assertEquals($field->boost, 1);
        $this->assertEquals($field->encoding, 'UTF-8');
        $this->assertEquals($field->isBinary,    false);
        $this->assertEquals($field->isIndexed,   false);
        $this->assertEquals($field->isStored,    true);
        $this->assertEquals($field->isTokenized, false);

        $this->assertEquals($field->name, 'field');
        $this->assertEquals($field->value, 'value');
    }

    public function testUnStored()
    {
        $field = Document\Field::UnStored('field', 'value');

        $this->assertEquals($field->boost, 1);
        $this->assertEquals($field->encoding, 'UTF-8');
        $this->assertEquals($field->isBinary,    false);
        $this->assertEquals($field->isIndexed,   true);
        $this->assertEquals($field->isStored,    false);
        $this->assertEquals($field->isTokenized, true);

        $this->assertEquals($field->name, 'field');
        $this->assertEquals($field->value, 'value');
    }

    public function testEncoding()
    {
        // forcing filter to UTF-8
        $utf8text = iconv('UTF-8', 'UTF-8', 'Words with umlauts: åãü...');

        $iso8859_1 = iconv('UTF-8', 'ISO-8859-1', $utf8text);
        $field = Document\Field::Text('field', $iso8859_1, 'ISO-8859-1');

        $this->assertEquals($field->encoding, 'ISO-8859-1');

        $this->assertEquals($field->name, 'field');
        $this->assertEquals($field->value, $iso8859_1);
        $this->assertEquals($field->getUtf8Value(), $utf8text);
    }
}

