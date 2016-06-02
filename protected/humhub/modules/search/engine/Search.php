<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\engine;

use Yii;
use humhub\modules\search\interfaces\Searchable;
use humhub\modules\content\models\Content;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\user\models\User;
use humhub\modules\space\models\Space;

/**
 * Description of HSearchComponent
 *
 * @since 0.12
 * @author luke
 */
abstract class Search extends \yii\base\Component
{

    const EVENT_ON_REBUILD = 'onRebuild';
    const DOCUMENT_TYPE_USER = 'user';
    const DOCUMENT_TYPE_SPACE = 'space';
    const DOCUMENT_TYPE_CONTENT = 'content';
    const DOCUMENT_TYPE_OTHER = 'other';
    const DOCUMENT_VISIBILITY_PUBLIC = 'public';
    const DOCUMENT_VISIBILITY_PRIVATE = 'private';

    /**
     * @var int the minimum length of a search token
     */
    public $minQueryTokenLength = 2;

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
     *      limitSpaces         Array (Limit Content to given Spaces(
     *      filters             Array (Additional filter Field=>Value)
     *
     * @param type $query
     * @param array $options
     * @return SearchResultSet
     */
    abstract public function find($query, Array $options);

    /**
     * Stores an object in search index.
     *
     * @param Searchable $object
     */
    abstract public function add(Searchable $object);

    /**
     * Updates an object in search index.
     *
     * @param Searchable $object
     */
    abstract public function update(Searchable $object);

    /**
     * Deletes an object from search.
     *
     * @param Searchable $object
     */
    abstract public function delete(Searchable $object);

    /**
     * Deletes all objects from search index.
     */
    abstract public function flush();

    /**
     * Rebuilds search index
     */
    public function rebuild()
    {
        $this->flush();
        $this->trigger(self::EVENT_ON_REBUILD);
        $this->optimize();
    }

    /**
     * Optimizes the search index.
     * Default implementation does nothing, may be overidden by child classes.
     */
    public function optimize()
    {
        
    }

    protected function getMetaInfoArray(Searchable $obj)
    {
        $meta = array();
        $meta['type'] = $this->getDocumentType($obj);
        $meta['pk'] = $obj->getPrimaryKey();
        $meta['model'] = $obj->className();

        if ($obj instanceof \humhub\modules\content\components\ContentContainerActiveRecord) {
            $meta['containerModel'] = $obj->className();
            $meta['containerPk'] = $obj->id;
        }

        // Add content related meta data
        if ($meta['type'] == self::DOCUMENT_TYPE_CONTENT) {
            if ($obj->content->container !== null) {
                $meta['containerModel'] = $obj->content->container->className();
                $meta['containerPk'] = $obj->content->container->id;
                if ($obj->content->visibility == Content::VISIBILITY_PRIVATE) {
                    $meta['visibility'] = self::DOCUMENT_VISIBILITY_PRIVATE;
                } else {
                    $meta['visibility'] = self::DOCUMENT_VISIBILITY_PUBLIC;
                }
            } else {
                $meta['visibility'] = self::DOCUMENT_VISIBILITY_PRIVATE;
            }
        } elseif ($meta['type'] == self::DOCUMENT_TYPE_SPACE && $obj->visibility == Space::VISIBILITY_NONE) {
            $meta['visibility'] = self::DOCUMENT_VISIBILITY_PRIVATE;
        } else {
            $meta['visibility'] = self::DOCUMENT_VISIBILITY_PUBLIC;
        }

        return $meta;
    }

    protected function getDocumentType(Searchable $obj)
    {
        if ($obj instanceof Space) {
            return self::DOCUMENT_TYPE_SPACE;
        } elseif ($obj instanceof User) {
            return self::DOCUMENT_TYPE_USER;
        } elseif ($obj instanceof ContentActiveRecord) {
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
            $options['pageSize'] = Yii::$app->settings->get('paginationSize');

        if (!isset($options['checkPermissions'])) {
            $options['checkPermissions'] = true;
        }

        if (!isset($options['limitSpaces'])) {
            $options['limitSpaces'] = array();
        }

        return $options;
    }

}
