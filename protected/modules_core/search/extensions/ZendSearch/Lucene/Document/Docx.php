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
use ZendSearch\Lucene\Document\Exception\InvalidArgumentException;
use ZendSearch\Lucene\Exception\ExtensionNotLoadedException;
use ZendSearch\Lucene\Exception\RuntimeException;

/**
 * Docx document.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Document
 */
class Docx extends AbstractOpenXML
{
    /**
     * Xml Schema - WordprocessingML
     *
     * @var string
     */
    const SCHEMA_WORDPROCESSINGML = 'http://schemas.openxmlformats.org/wordprocessingml/2006/main';

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
        $documentBody = array();
        $coreProperties = array();

        // Open AbstractOpenXML package
        $package = new \ZipArchive();
        $package->open($fileName);

        // Read relations and search for officeDocument
        $relationsXml = $package->getFromName('_rels/.rels');
        if ($relationsXml === false) {
            throw new RuntimeException('Invalid archive or corrupted .docx file.');
        }

        // Prevent php from loading remote resources
        $loadEntities = libxml_disable_entity_loader(true);

        $relations = simplexml_load_string($relationsXml);

        // Restore entity loader state
        libxml_disable_entity_loader($loadEntities);

        foreach($relations->Relationship as $rel) {
            if ($rel ["Type"] == AbstractOpenXML::SCHEMA_OFFICEDOCUMENT) {
                // Found office document! Read in contents...
                $contents = simplexml_load_string($package->getFromName(
                                                                $this->absoluteZipPath(dirname($rel['Target'])
                                                              . '/'
                                                              . basename($rel['Target']))
                                                                       ));

                $contents->registerXPathNamespace('w', self::SCHEMA_WORDPROCESSINGML);
                $paragraphs = $contents->xpath('//w:body/w:p');

                foreach ($paragraphs as $paragraph) {
                    $runs = $paragraph->xpath('.//w:r/*[name() = "w:t" or name() = "w:br"]');

                    if ($runs === false) {
                        // Paragraph doesn't contain any text or breaks
                        continue;
                    }

                    foreach ($runs as $run) {
                     if ($run->getName() == 'br') {
                         // Break element
                         $documentBody[] = ' ';
                     } else {
                         $documentBody[] = (string)$run;
                     }
                    }

                    // Add space after each paragraph. So they are not bound together.
                    $documentBody[] = ' ';
                }

                break;
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
            $this->addField(Field::Text('body', implode('', $documentBody), 'UTF-8'));
        } else {
            $this->addField(Field::UnStored('body', implode('', $documentBody), 'UTF-8'));
        }

        // Store meta data properties
        foreach ($coreProperties as $key => $value) {
            $this->addField(Field::Text($key, $value, 'UTF-8'));
        }

        // Store title (if not present in meta data)
        if (! isset($coreProperties['title'])) {
            $this->addField(Field::Text('title', $fileName, 'UTF-8'));
        }
    }

    /**
     * Load Docx document from a file
     *
     * @param string  $fileName
     * @param boolean $storeContent
     * @throws \ZendSearch\Lucene\Document\Exception\InvalidArgumentException
     * @return \ZendSearch\Lucene\Document\Docx
     */
    public static function loadDocxFile($fileName, $storeContent = false)
    {
        if (!is_readable($fileName)) {
            throw new InvalidArgumentException('Provided file \'' . $fileName . '\' is not readable.');
        }

        return new self($fileName, $storeContent);
    }
}
