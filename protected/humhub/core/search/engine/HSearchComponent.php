<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * Description of HSearchComponent
 *
 * @since 0.12
 * @author luke
 */
abstract class HSearchComponent extends CApplicationComponent
{

    const DOCUMENT_TYPE_USER = 'user';
    const DOCUMENT_TYPE_SPACE = 'space';
    const DOCUMENT_TYPE_CONTENT = 'content';
    const DOCUMENT_TYPE_OTHER = 'other';

    public function __construct()
    {
        Yii::app()->interceptor->intercept($this);
    }

    /**
     * Retrieves results from search
     * 
     * Available options:
     *      page            
     *      pageSize
     * 
     *      sortField           Mixed String/Array
     *      model               Mixed String/Array
     *      type                Mixed String/Array
     *      checkPermissions    boolean (TRUE/false)
     *      limitSpaces         Arraz (Limit Content to given Spaces(
     * 
     * @param type $query
     * @param array $options 
     * @return SearchResultSet
     */
    public function find($query, Array $options)
    {
        
    }

    /**
     * Stores an object in search.
     * 
     * @param ISearchable $object
     */
    public function add(ISearchable $object)
    {
        
    }

    /**
     * Updates an object in search index.
     * 
     * @param ISearchable $object
     */
    public function update(ISearchable $object)
    {
        
    }

    /**
     * Deletes an object in search.
     * 
     * @param ISearchable $object
     */
    public function delete(ISearchable $object)
    {
        
    }

    /**
     * Deletes all objects from search index.
     * 
     * @param ISearchable $object
     */
    public function flush()
    {
        
    }

    /**
     * Rebuilds search index
     */
    public function rebuild()
    {
        $this->flush();
        if ($this->hasEventHandler('onRebuild'))
            $this->onRebuild(new CEvent($this));

        $this->optimize();
    }

    /**
     * Optimizes the search index
     */
    public function optimize()
    {
        
    }

    /**
     * This event is raised after the rebuild is performed.
     * @param CEvent $event the event parameter
     */
    public function onRebuild($event)
    {
        $this->raiseEvent('onRebuild', $event);
    }

    protected function getMetaInfoArray(ISearchable $obj)
    {
        $meta = array();
        $meta['type'] = $this->getDocumentType($obj);
        $meta['pk'] = $obj->getPrimaryKey();
        $meta['model'] = get_class($obj);

        // Add content related meta data
        if ($meta['type'] == self::DOCUMENT_TYPE_CONTENT) {
            $meta['containerModel'] = get_class($obj->content->container);
            $meta['containerPk'] = $obj->content->container->id;
            $meta['visibility'] = $obj->content->visibility;
        } else {
            $meta['visibility'] = Content::VISIBILITY_PUBLIC;
        }

        return $meta;
    }

    protected function getDocumentType(ISearchable $obj)
    {
        if ($obj instanceof Space) {
            return self::DOCUMENT_TYPE_SPACE;
        } elseif ($obj instanceof User) {
            return self::DOCUMENT_TYPE_USER;
        } elseif ($obj instanceof HActiveRecordContent) {
            return self::DOCUMENT_TYPE_CONTENT;
        } else {
            return self::DOCUMENT_TYPE_OTHER;
        }
    }

    protected function setDefaultFindOptions($options)
    {
        if (!isset($options['page']) || $options['page'] == "")
            $options['page'] = 1;

        if (!isset($options['pageSize']) || $options['pageSize'] == "")
            $options['pageSize'] = HSetting::Get('paginationSize');

        if (!isset($options['checkPermissions'])) {
            $options['checkPermissions'] = true;
        }

        if (!isset($options['limitSpaces'])) {
            $options['limitSpaces'] = array();
        }

        return $options;
    }

}
