<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2012 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 * @package   Zend_Search
 */

namespace ZendSearch\Lucene\Document;

use ZendSearch\Lucene;
use ZendSearch\Lucene\Exception\ExtensionNotLoadedException;
use ZendSearch\Lucene\Exception\RuntimeException;

/**
 * Xlsx document.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Document
 */
class Xlsx extends AbstractOpenXML
{
    /**
     * Xml Schema - SpreadsheetML
     *
     * @var string
     */
    const SCHEMA_SPREADSHEETML = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';

    /**
     * Xml Schema - DrawingML
     *
     * @var string
     */
    const SCHEMA_DRAWINGML = 'http://schemas.openxmlformats.org/drawingml/2006/main';

    /**
     * Xml Schema - Shared Strings
     *
     * @var string
     */
    const SCHEMA_SHAREDSTRINGS = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings';

    /**
     * Xml Schema - Worksheet relation
     *
     * @var string
     */
    const SCHEMA_WORKSHEETRELATION = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet';

    /**
     * Xml Schema - Slide notes relation
     *
     * @var string
     */
    const SCHEMA_SLIDENOTESRELATION = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/notesSlide';

    /**
     * Object constructor
     *
     * @param string  $fileName
     * @param boolean $storeContent
     * @throws \ZendSearch\Lucene\Exception\ExtensionNotLoadedException
     * @throws \ZendSearch\Lucene\Exception\RuntimeException
     */
    private function __construct($fileName, $storeContent)
    {
        if (!class_exists('ZipArchive', false)) {
            throw new ExtensionNotLoadedException(
                'MS Office documents processing functionality requires Zip extension to be loaded'
            );
        }

        // Document data holders
        $sharedStrings = array();
        $worksheets = array();
        $documentBody = array();
        $coreProperties = array();

        // Open AbstractOpenXML package
        $package = new \ZipArchive();
        $package->open($fileName);

        // Read relations and search for officeDocument
        $relationsXml = $package->getFromName('_rels/.rels');
        if ($relationsXml === false) {
            throw new RuntimeException('Invalid archive or corrupted .xlsx file.');
        }

        // Prevent php from loading remote resources
        $loadEntities = libxml_disable_entity_loader(true);

        $relations = simplexml_load_string($relationsXml);

        // Restore entity loader state
        libxml_disable_entity_loader($loadEntities);

        foreach ($relations->Relationship as $rel) {
            if ($rel["Type"] == AbstractOpenXML::SCHEMA_OFFICEDOCUMENT) {
                // Found office document! Read relations for workbook...
                $workbookRelations = simplexml_load_string($package->getFromName( $this->absoluteZipPath(dirname($rel["Target"]) . "/_rels/" . basename($rel["Target"]) . ".rels")) );
                $workbookRelations->registerXPathNamespace("rel", AbstractOpenXML::SCHEMA_RELATIONSHIP);

                // Read shared strings
                $sharedStringsPath = $workbookRelations->xpath("rel:Relationship[@Type='" . self::SCHEMA_SHAREDSTRINGS . "']");
                $sharedStringsPath = (string)$sharedStringsPath[0]['Target'];
                $xmlStrings = simplexml_load_string($package->getFromName( $this->absoluteZipPath(dirname($rel["Target"]) . "/" . $sharedStringsPath)) );
                if (isset($xmlStrings) && isset($xmlStrings->si)) {
                    foreach ($xmlStrings->si as $val) {
                        if (isset($val->t)) {
                            $sharedStrings[] = (string)$val->t;
                        } elseif (isset($val->r)) {
                            $sharedStrings[] = $this->_parseRichText($val);
                        }
                    }
                }

                // Loop relations for workbook and extract worksheets...
                foreach ($workbookRelations->Relationship as $workbookRelation) {
                    if ($workbookRelation["Type"] == self::SCHEMA_WORKSHEETRELATION) {
                        $worksheets[ str_replace( 'rId', '', (string)$workbookRelation["Id"]) ] = simplexml_load_string(
                            $package->getFromName( $this->absoluteZipPath(dirname($rel["Target"]) . "/" . dirname($workbookRelation["Target"]) . "/" . basename($workbookRelation["Target"])) )
                        );
                    }
                }

                break;
            }
        }

        // Sort worksheets
        ksort($worksheets);

        // Extract contents from worksheets
        foreach ($worksheets as $sheetKey => $worksheet) {
            foreach ($worksheet->sheetData->row as $row) {
                foreach ($row->c as $c) {
                    // Determine data type
                    $dataType = (string)$c["t"];
                    switch ($dataType) {
                        case "s":
                            // Value is a shared string
                            if ((string)$c->v != '') {
                                $value = $sharedStrings[intval($c->v)];
                            } else {
                                $value = '';
                            }

                            break;

                        case "b":
                            // Value is boolean
                            $value = (string)$c->v;
                            if ($value == '0') {
                                $value = false;
                            } elseif ($value == '1') {
                                $value = true;
                            } else {
                                $value = (bool)$c->v;
                            }

                            break;

                        case "inlineStr":
                            // Value is rich text inline
                            $value = $this->_parseRichText($c->is);

                            break;

                        case "e":
                            // Value is an error message
                            if ((string)$c->v != '') {
                                $value = (string)$c->v;
                            } else {
                                $value = '';
                            }

                            break;

                        default:
                            // Value is a string
                            $value = (string)$c->v;

                            // Check for numeric values
                            if (is_numeric($value) && $dataType != 's') {
                                if ($value == (int)$value) $value = (int)$value;
                                elseif ($value == (float)$value) $value = (float)$value;
                                elseif ($value == (double)$value) $value = (double)$value;
                            }
                    }

                    $documentBody[] = $value;
                }
            }
        }

        // Read core properties
        $coreProperties = $this->extractMetaData($package);

        // Close file
        $package->close();

        // Store filename
        $this->addField(Field::Text('filename', $fileName, 'UTF-8'));

        // Store contents
        if ($storeContent) {
            $this->addField(Field::Text('body', implode(' ', $documentBody), 'UTF-8'));
        } else {
            $this->addField(Field::UnStored('body', implode(' ', $documentBody), 'UTF-8'));
        }

        // Store meta data properties
        foreach ($coreProperties as $key => $value) {
            $this->addField(Field::Text($key, $value, 'UTF-8'));
        }

        // Store title (if not present in meta data)
        if (!isset($coreProperties['title'])) {
            $this->addField(Field::Text('title', $fileName, 'UTF-8'));
        }
    }

    /**
     * Parse rich text XML
     *
     * @param \SimpleXMLElement $is
     * @return string
     */
    private function _parseRichText($is = null)
    {
        $value = array();

        if (isset($is->t)) {
            $value[] = (string)$is->t;
        } else {
            foreach ($is->r as $run) {
                $value[] = (string)$run->t;
            }
        }

        return implode('', $value);
    }

    /**
     * Load Xlsx document from a file
     *
     * @param string  $fileName
     * @param boolean $storeContent
     * @return \ZendSearch\Lucene\Document\Xlsx
     */
    public static function loadXlsxFile($fileName, $storeContent = false)
    {
        return new self($fileName, $storeContent);
    }
}
