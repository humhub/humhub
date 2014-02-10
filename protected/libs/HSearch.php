<?php

/**
 * HumHub
 * Copyright © 2014 The HumHub Project
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 */

/**
 * Singleton HSearch Class provides search functions to the application.
 *
 * @package humhub.libs
 * @since 0.5
 * @author Luke
 */
class HSearch extends CComponent {

    /**
     * @var HSearch instance
     */
    static private $instance = null;

    /**
     * @var Zend_Search_Lucene_Index instance
     */
    public $index = null;

    /**
     * Returns the singleton instance of this class
     *
     * @return HSearch
     */
    static public function getInstance() {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    /**
     * Constructor
     *
     * Force singleton use.
     */
    private function __construct() {
        Yii::app()->interceptor->intercept($this);
    }

    /**
     * Cloning
     *
     * Force singleton use.
     */
    private function __clone() {

    }

    /**
     * Adds a new model to the search index
     *
     * @param ISearchable $obj
     * @throws CException
     */
    public function addModel($obj) {

        if (!$obj instanceof ISearchable) {
            throw new CException("Invalid Object given, must implement ISearchable");
        }

        // Get Primary Key
        $attributes = $obj->getSearchAttributes();
        $guid = $attributes['model'] . $attributes['pk'];

        $index = $this->getIndex();

        $doc = new Zend_Search_Lucene_Document();
        $doc->addField(Zend_Search_Lucene_Field::Text('guid', $guid));
        $doc->addField(Zend_Search_Lucene_Field::Text('belongsToType', $attributes['belongsToType']));
        $doc->addField(Zend_Search_Lucene_Field::Text('belongsToId', $attributes['belongsToId']));
        $doc->addField(Zend_Search_Lucene_Field::Text('belongsToGuid', $attributes['belongsToGuid']));
        $doc->addField(Zend_Search_Lucene_Field::Text('model', $attributes['model']));
        $doc->addField(Zend_Search_Lucene_Field::Text('pk', $attributes['pk']));
        $doc->addField(Zend_Search_Lucene_Field::Text('title', $attributes['title'], 'UTF-8'));
        $doc->addField(Zend_Search_Lucene_Field::Text('url', $attributes['url'], 'UTF-8'));

        // Remove all internal attributes
        unset($attributes['belongsToType']);
        unset($attributes['belongsToId']);
        unset($attributes['belongsToGuid']);
        unset($attributes['model']);
        unset($attributes['pk']);
        unset($attributes['title']);
        unset($attributes['url']);

        foreach ($attributes as $key => $val) {
            $doc->addField(Zend_Search_Lucene_Field::Text($key, $val, 'UTF-8'));
        }

        #print "\t ADD ".$guid." \n";
        $index->addDocument($doc);


        $index->commit();
    }

    /**
     * Deletes a model from the search index
     *
     * @param ISearchable $obj
     * @throws CException
     */
    public function deleteModel($obj) {

        if (!$obj instanceof ISearchable) {
            throw new CException("Invalid Object given, must implement ISearchable");
        }

        // Get Primary Key
        $attributes = $obj->getSearchAttributes();
        $guid = $attributes['model'] . $attributes['pk'];

        $index = $this->getIndex();

        // Remove from Index
        $hits = $index->find('guid:' . $guid);
        foreach ($hits as $hit) {
            #print "\t DELETE ".$guid." delete: ".$hit->id.$hit->getDocument()->getField('guid')->value."\n";
            $index->delete($hit->id);
        }

        $index->commit();
    }

    /**
     * Optimized the Index
     */
    public function Optimize() {
        $index = $this->getIndex();
        $index->optimize();
    }

    /**
     * Searches the index
     *
     * @param String $key lucene query
     * @param String $sort lucene sort
     * @return type
     */
    public function find($key, $sort = null) {
        $index = $this->getIndex();

        if ($sort != null)
            return $index->find($key, $sort);
        else
            return $index->find($key);
    }

    /**
     * Returns the index
     *
     * @return type
     */
    public function getIndex() {

        if ($this->index != null)
            return $this->index;

        Zend_Search_Lucene_Search_QueryParser::setDefaultEncoding('utf-8');

        // Important also for GUID Searches (e.g. delete)
        Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());
        Zend_Search_Lucene_Search_QueryParser::setDefaultOperator(Zend_Search_Lucene_Search_QueryParser::B_AND);

        $searchIndexPath = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . "searchdb" . DIRECTORY_SEPARATOR;

        try {
            $index = Zend_Search_Lucene::open($searchIndexPath);
        } catch (Exception $ex) {
            $index = Zend_Search_Lucene::create($searchIndexPath);
        }

        $this->index = $index;

        return $index;
    }

    /**
     * Rebuilds the search index
     *
     * This fires an event HSearch onRebuild. Modules like user, space or post
     * should catch it and add their models.
     */
    public function rebuild() {
        $this->flushIndex();
        if ($this->hasEventHandler('onRebuild'))
            $this->onRebuild(new CEvent($this));

        $this->Optimize();
    }

    /**
     * This event is raised after the rebuild is performed.
     * @param CEvent $event the event parameter
     */
    public function onRebuild($event) {
        $this->raiseEvent('onRebuild', $event);
    }

    /**
     * Flushes the index, delete all data
     */
    public function flushIndex() {
        // Delete all index files
        $searchIndexPath = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . "searchdb" . DIRECTORY_SEPARATOR;

        // Try autocreate search db path
        if (!is_dir($searchIndexPath)) {
            mkdir($searchIndexPath);
        }

        foreach (new DirectoryIterator($searchIndexPath) as $fileInfo) {
            if ($fileInfo->isDot())
                continue;

            if ($fileInfo->getFilename() == ".svn")
                continue;

            unlink($searchIndexPath . $fileInfo->getFilename());
        }

        $this->index = null;
    }

}

?>
