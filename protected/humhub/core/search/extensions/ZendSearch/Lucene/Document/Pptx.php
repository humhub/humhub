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
 * Pptx document.
 *
 * @category   Zend
 * @package    Zend_Search_Lucene
 * @subpackage Document
 */
class Pptx extends AbstractOpenXML
{
    /**
     * Xml Schema - PresentationML
     *
     * @var string
     */
    const SCHEMA_PRESENTATIONML = 'http://schemas.openxmlformats.org/presentationml/2006/main';

    /**
     * Xml Schema - DrawingML
     *
     * @var string
     */
    const SCHEMA_DRAWINGML = 'http://schemas.openxmlformats.org/drawingml/2006/main';

    /**
     * Xml Schema - Slide relation
     *
     * @var string
     */
    const SCHEMA_SLIDERELATION = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships/slide';

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
            throw new ExtensionNotLoadedException('MS Office documents processing functionality requires Zip extension to be loaded');
        }

        // Document data holders
        $slides = array();
        $slideNotes = array();
        $documentBody = array();
        $coreProperties = array();

        // Open AbstractOpenXML package
        $package = new \ZipArchive();
        $package->open($fileName);

        // Read relations and search for officeDocument
        $relationsXml = $package->getFromName('_rels/.rels');
        if ($relationsXml === false) {
            throw new RuntimeException('Invalid archive or corrupted .pptx file.');
        }

        // Prevent php from loading remote resources
        $loadEntities = libxml_disable_entity_loader(true);

        $relations = simplexml_load_string($relationsXml);

        // Restore entity loader state
        libxml_disable_entity_loader($loadEntities);

        foreach ($relations->Relationship as $rel) {
            if ($rel["Type"] == AbstractOpenXML::SCHEMA_OFFICEDOCUMENT) {
                // Found office document! Search for slides...
                $slideRelations = simplexml_load_string($package->getFromName( $this->absoluteZipPath(dirname($rel["Target"]) . "/_rels/" . basename($rel["Target"]) . ".rels")) );
                foreach ($slideRelations->Relationship as $slideRel) {
                    if ($slideRel["Type"] == self::SCHEMA_SLIDERELATION) {
                        // Found slide!
                        $slides[ str_replace( 'rId', '', (string)$slideRel["Id"] ) ] = simplexml_load_string(
                            $package->getFromName( $this->absoluteZipPath(dirname($rel["Target"]) . "/" . dirname($slideRel["Target"]) . "/" . basename($slideRel["Target"])) )
                        );

                        // Search for slide notes
                        $slideNotesRelations = simplexml_load_string($package->getFromName( $this->absoluteZipPath(dirname($rel["Target"]) . "/" . dirname($slideRel["Target"]) . "/_rels/" . basename($slideRel["Target"]) . ".rels")) );
                        foreach ($slideNotesRelations->Relationship as $slideNoteRel) {
                            if ($slideNoteRel["Type"] == self::SCHEMA_SLIDENOTESRELATION) {
                                // Found slide notes!
                                $slideNotes[ str_replace( 'rId', '', (string)$slideRel["Id"] ) ] = simplexml_load_string(
                                    $package->getFromName( $this->absoluteZipPath(dirname($rel["Target"]) . "/" . dirname($slideRel["Target"]) . "/" . dirname($slideNoteRel["Target"]) . "/" . basename($slideNoteRel["Target"])) )
                                );

                                break;
                            }
                        }
                    }
                }

                break;
            }
        }

        // Sort slides
        ksort($slides);
        ksort($slideNotes);

        // Extract contents from slides
        foreach ($slides as $slideKey => $slide) {
            // Register namespaces
            $slide->registerXPathNamespace("p", self::SCHEMA_PRESENTATIONML);
            $slide->registerXPathNamespace("a", self::SCHEMA_DRAWINGML);

            // Fetch all text
            $textElements = $slide->xpath('//a:t');
            foreach ($textElements as $textElement) {
                $documentBody[] = (string)$textElement;
            }

            // Extract contents from slide notes
            if (isset($slideNotes[$slideKey])) {
                // Fetch slide note
                $slideNote = $slideNotes[$slideKey];

                // Register namespaces
                $slideNote->registerXPathNamespace("p", self::SCHEMA_PRESENTATIONML);
                $slideNote->registerXPathNamespace("a", self::SCHEMA_DRAWINGML);

                // Fetch all text
                $textElements = $slideNote->xpath('//a:t');
                foreach ($textElements as $textElement) {
                    $documentBody[] = (string)$textElement;
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
     * Load Pptx document from a file
     *
     * @param string  $fileName
     * @param boolean $storeContent
     * @return \ZendSearch\Lucene\Document\Pptx
     */
    public static function loadPptxFile($fileName, $storeContent = false)
    {
        return new self($fileName, $storeContent);
    }
}
