<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

/**
 * ZendLucenceSearch Engine
 *
 * @since 0.12
 * @author luke
 */
class ZendLuceneSearch extends HSearchComponent
{

    public $index = null;

    public function add(ISearchable $obj)
    {
        // Get Primary Key
        $attributes = $obj->getSearchAttributes();

        $index = $this->getIndex();

        $doc = new ZendSearch\Lucene\Document();

        // Add Meta Data fields
        foreach ($this->getMetaInfoArray($obj) as $fieldName => $fieldValue) {
            $doc->addField(ZendSearch\Lucene\Document\Field::Text($fieldName, $fieldValue));
        }

        // Add provided search infos
        foreach ($attributes as $key => $val) {
            $doc->addField(ZendSearch\Lucene\Document\Field::Text($key, $val, 'UTF-8'));
        }

        if ($obj instanceof HActiveRecordContent) {
            $comments = "";
            foreach (Comment::model()->findAllByAttributes(array('object_id' => $obj->getPrimaryKey(), 'object_model' => get_class($obj))) as $comment) {
                $comments .= " ".$comment->message;
            }
            $doc->addField(ZendSearch\Lucene\Document\Field::Text('comments', $comments, 'UTF-8'));
        }

        if (Yii::app() instanceof CConsoleApplication) {
            print ".";
        }
        
        $index->addDocument($doc);
        $index->commit();
    }

    public function update(ISearchable $object)
    {
        $this->delete($object);
        $this->add($object);
        $this->optimize();
    }

    public function delete(ISearchable $obj)
    {
        $index = $this->getIndex();

        $hits = $index->find('pk:' . $obj->getPrimaryKey() . " model:" . get_class($obj));
        foreach ($hits as $hit) {
            $index->delete($hit->id);
        }

        $index->commit();
    }

    public function flush()
    {
        $indexPath = $this->getIndexPath();
        foreach (new DirectoryIterator($indexPath) as $fileInfo) {
            if ($fileInfo->isDot())
                continue;
            unlink($indexPath . $fileInfo->getFilename());
        }

        $this->index = null;
    }

    public function find($keyword, Array $options)
    {

        $options = $this->setDefaultFindOptions($options);
        $index = $this->getIndex();
        $keyword = str_replace(array('*', '?', '_', '$'), ' ', strtolower($keyword));

        if (!isset($options['sortField']) || $options['sortField'] == "") {
            $hits = new ArrayObject($index->find($this->buildQuery($keyword, $options)));
        } else {
            $hits = new ArrayObject($index->find($this->buildQuery($keyword, $options), $options['sortField']));
        }

        $resultSet = new SearchResultSet();
        $resultSet->total = count($hits);
        $resultSet->pageSize = $options['pageSize'];
        $resultSet->page = $options['page'];

        $hits = new LimitIterator($hits->getIterator(), ($options['page'] - 1) * $options['pageSize'], $options['pageSize']);
        foreach ($hits as $hit) {
            $document = $hit->getDocument();

            $result = new SearchResult();
            $result->model = $document->getField('model')->getUtf8Value();
            $result->pk = $document->getField('pk')->getUtf8Value();
            $result->type = $document->getField('type')->getUtf8Value();

            $resultSet->results[] = $result;
        }

        return $resultSet;
    }

    protected function buildQuery($keyword, $options)
    {
        $query = new ZendSearch\Lucene\Search\Query\Boolean();
        foreach (explode(" ", $keyword) as $k) {
            // Require at least 3 non-wildcard characters
            if (strlen($k) > 2) {
                $term = new ZendSearch\Lucene\Index\Term($k . "*");
                $query->addSubquery(new ZendSearch\Lucene\Search\Query\Wildcard($term), true);
            }
        }
        // Add model filter
        if (isset($options['model']) && $options['model'] != "") {
            if (is_array($options['model'])) {
                $boolQuery = new ZendSearch\Lucene\Search\Query\MultiTerm();
                foreach ($options['model'] as $model) {
                    $boolQuery->addTerm(new ZendSearch\Lucene\Index\Term(strtolower($model), 'model'));
                }
                $query->addSubquery($boolQuery, true);
            } else {
                $term = new ZendSearch\Lucene\Index\Term(strtolower($options['model']), 'model');
                $query->addSubquery(new ZendSearch\Lucene\Search\Query\Term($term), true);
            }
        }

        // Add type filter
        if (isset($options['type']) && $options['type'] != "") {
            if (is_array($options['type'])) {
                $boolQuery = new ZendSearch\Lucene\Search\Query\MultiTerm();
                foreach ($options['type'] as $model) {
                    $boolQuery->addTerm(new ZendSearch\Lucene\Index\Term(strtolower($type), 'type'));
                }
                $query->addSubquery($boolQuery, true);
            } else {
                $term = new ZendSearch\Lucene\Index\Term(strtolower($options['type']), 'type');
                $query->addSubquery(new ZendSearch\Lucene\Search\Query\Term($term), true);
            }
        }


        if ($options['checkPermissions']) {
            $permissionQuery = new ZendSearch\Lucene\Search\Query\Boolean();

            //--- Public Content
            $permissionQuery->addSubquery(new ZendSearch\Lucene\Search\Query\Term(new ZendSearch\Lucene\Index\Term(Content::VISIBILITY_PUBLIC, 'visibility')));

            //--- Private Space Content
            $privateSpaceContentQuery = new ZendSearch\Lucene\Search\Query\Boolean();
            $privateSpaceContentQuery->addSubquery(new ZendSearch\Lucene\Search\Query\Term(new ZendSearch\Lucene\Index\Term(Content::VISIBILITY_PRIVATE, 'visibility')), true);
            $privateSpaceContentQuery->addSubquery(new ZendSearch\Lucene\Search\Query\Term(new ZendSearch\Lucene\Index\Term('space', 'containerModel')), true);
            $privateSpacesListQuery = new ZendSearch\Lucene\Search\Query\MultiTerm();

            foreach (SpaceMembership::GetUserSpaces() as $space) {
                $privateSpacesListQuery->addTerm(new ZendSearch\Lucene\Index\Term($space->id, 'containerPk'));
            }

            $privateSpaceContentQuery->addSubquery($privateSpacesListQuery, true);

            $permissionQuery->addSubquery($privateSpaceContentQuery);
            $query->addSubquery($permissionQuery, true);
        }

        if (count($options['limitSpaces']) > 0) {

            $spaceBaseQuery = new ZendSearch\Lucene\Search\Query\Boolean();
            $spaceBaseQuery->addSubquery(new ZendSearch\Lucene\Search\Query\Term(new ZendSearch\Lucene\Index\Term('space', 'containerModel')), true);
            $spaceIdQuery = new ZendSearch\Lucene\Search\Query\MultiTerm();
            foreach ($options['limitSpaces'] as $space) {
                $spaceIdQuery->addTerm(new ZendSearch\Lucene\Index\Term($space->id, 'containerPk'));
            }
            $spaceBaseQuery->addSubquery($spaceIdQuery, true);
            $query->addSubquery($spaceBaseQuery, true);
        }


        return $query;
    }

    public function optimize()
    {
        $index = $this->getIndex();
        $index->optimize();
    }

    protected function getIndex()
    {

        if ($this->index != null)
            return $this->index;

        Yii::setPathOfAlias('ZendSearch', Yii::getPathOfAlias('application.modules_core.search.extensions.ZendSearch'));

        ZendSearch\Lucene\Search\QueryParser::setDefaultEncoding('utf-8');
        ZendSearch\Lucene\Analysis\Analyzer\Analyzer::setDefault(new ZendSearch\Lucene\Analysis\Analyzer\Common\Utf8Num\CaseInsensitive());
        ZendSearch\Lucene\Search\QueryParser::setDefaultOperator(ZendSearch\Lucene\Search\QueryParser::B_AND);

        try {
            $index = ZendSearch\Lucene\Lucene::open($this->getIndexPath());
        } catch (Exception $ex) {
            $index = ZendSearch\Lucene\Lucene::create($this->getIndexPath());
        }

        $this->index = $index;
        return $index;
    }

    protected function getIndexPath()
    {
        $path = Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . "searchdb" . DIRECTORY_SEPARATOR;

        if (!is_dir($path)) {
            mkdir($path);
        }

        return $path;
    }

}
