<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\engine;

use Yii;
use humhub\modules\search\interfaces\Searchable;
use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\search\libs\SearchResult;
use humhub\modules\search\libs\SearchResultSet;
use humhub\modules\comment\models\Comment;
use humhub\modules\space\models\Space;

/**
 * ZendLucenceSearch Engine
 *
 * @since 0.12
 * @author luke
 */
class ZendLuceneSearch extends Search
{

    /**
     * @var \ZendSearch\Lucene\SearchIndexInterface the lucence index
     */
    public $index = null;

    public function add(Searchable $obj)
    {
        // Get Primary Key
        $attributes = $obj->getSearchAttributes();

        $index = $this->getIndex();

        $doc = new \ZendSearch\Lucene\Document();

        // Add Meta Data fields
        foreach ($this->getMetaInfoArray($obj) as $fieldName => $fieldValue) {
            $doc->addField(\ZendSearch\Lucene\Document\Field::keyword($fieldName, $fieldValue));
        }

        // Add provided search infos
        foreach ($attributes as $key => $val) {
            if (is_array($val)) {
                $val = implode(" ", $val);
            }

            $doc->addField(\ZendSearch\Lucene\Document\Field::Text($key, $val, 'UTF-8'));
        }

        // Add comments - if record is content
        if ($obj instanceof ContentActiveRecord) {
            $comments = "";
            foreach (Comment::findAll(['object_id' => $obj->getPrimaryKey(), 'object_model' => $obj->className()]) as $comment) {
                $comments .= ' ' . $comment->user->displayName . ' ' . $comment->message;
            }
            $doc->addField(\ZendSearch\Lucene\Document\Field::unStored('comments', $comments, 'UTF-8'));
        }

        if (\Yii::$app->request->isConsoleRequest) {
            print ".";
        }

        $index->addDocument($doc);
        $index->commit();
    }

    public function update(Searchable $object)
    {
        $this->delete($object);
        $this->add($object);
    }

    public function delete(Searchable $obj)
    {
        $index = $this->getIndex();

        $query = new \ZendSearch\Lucene\Search\Query\MultiTerm();
        $query->addTerm(new \ZendSearch\Lucene\Index\Term($obj->className(), 'model'), true);
        $query->addTerm(new \ZendSearch\Lucene\Index\Term($obj->getPrimaryKey(), 'pk'), true);

        $hits = $index->find($query);
        foreach ($hits as $hit) {
            $index->delete($hit->id);
        }

        $index->commit();
    }

    public function flush()
    {
        $indexPath = $this->getIndexPath();
        foreach (new \DirectoryIterator($indexPath) as $fileInfo) {
            if ($fileInfo->isDot())
                continue;
            unlink($indexPath . DIRECTORY_SEPARATOR . $fileInfo->getFilename());
        }

        $this->index = null;
    }

    public function find($keyword, Array $options)
    {
        $options = $this->setDefaultFindOptions($options);

        $index = $this->getIndex();
        $keyword = str_replace(array('*', '?', '_', '$'), ' ', mb_strtolower($keyword, 'utf-8'));

        $query = $this->buildQuery($keyword, $options);
        if ($query === null) {
            return new SearchResultSet();
        }

        if (!isset($options['sortField']) || $options['sortField'] == "") {
            $hits = new \ArrayObject($index->find($query));
        } else {
            $hits = new \ArrayObject($index->find($query, $options['sortField']));
        }

        $resultSet = new SearchResultSet();
        $resultSet->total = count($hits);
        $resultSet->pageSize = $options['pageSize'];
        $resultSet->page = $options['page'];

        $hits = new \LimitIterator($hits->getIterator(), ($options['page'] - 1) * $options['pageSize'], $options['pageSize']);
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

    /**
     * Returns the lucence search query
     * 
     * @param string $keyword
     * @param array $options
     * @return \ZendSearch\Lucene\Search\Query\AbstractQuery
     */
    protected function buildQuery($keyword, $options)
    {
        // Allow *Token*
        \ZendSearch\Lucene\Search\Query\Wildcard::setMinPrefixLength(0);

        $query = new \ZendSearch\Lucene\Search\Query\Boolean();

        $emptyQuery = true;
        foreach (explode(" ", $keyword) as $k) {
            // Require a minimum of non-wildcard characters
            if (mb_strlen($k, Yii::$app->charset) >= $this->minQueryTokenLength) {
                $term = new \ZendSearch\Lucene\Index\Term("*$k*");
                $query->addSubquery(new \ZendSearch\Lucene\Search\Query\Wildcard($term), true);
                $emptyQuery = false;
            }
        }

        // if only too short keywords are given, the result is empty
        // when no keyword was given - show some results
        if ($emptyQuery && $keyword != '') {
            return null;
        }

        // Add model filter
        if (isset($options['model']) && $options['model'] != "") {
            if (is_array($options['model'])) {
                $boolQuery = new \ZendSearch\Lucene\Search\Query\MultiTerm();
                foreach ($options['model'] as $model) {
                    $boolQuery->addTerm(new \ZendSearch\Lucene\Index\Term($model, 'model'));
                }
                $query->addSubquery($boolQuery, true);
            } else {
                $term = new \ZendSearch\Lucene\Index\Term($options['model'], 'model');
                $query->addSubquery(new \ZendSearch\Lucene\Search\Query\Term($term), true);
            }
        }

        // Add type filter
        if (isset($options['type']) && $options['type'] != "") {
            if (is_array($options['type'])) {
                $boolQuery = new \ZendSearch\Lucene\Search\Query\MultiTerm();
                foreach ($options['type'] as $model) {
                    $boolQuery->addTerm(new \ZendSearch\Lucene\Index\Term($type), 'type');
                }
                $query->addSubquery($boolQuery, true);
            } else {
                $term = new \ZendSearch\Lucene\Index\Term($options['type'], 'type');
                $query->addSubquery(new \ZendSearch\Lucene\Search\Query\Term($term), true);
            }
        }

        // Add custom filters
        if (isset($options['filters']) && is_array($options['filters'])) {
            foreach ($options['filters'] as $field => $value) {
                $term = new \ZendSearch\Lucene\Index\Term($value, $field);
                $query->addSubquery(new \ZendSearch\Lucene\Search\Query\Term($term), true);
            }
        }


        if ($options['checkPermissions'] && !Yii::$app->request->isConsoleRequest) {

            $permissionQuery = new \ZendSearch\Lucene\Search\Query\Boolean();

            if (Yii::$app->user->isGuest) {

                // Guest Content
                $guestContentQuery = new \ZendSearch\Lucene\Search\Query\Boolean();
                $guestContentQuery->addSubquery(new \ZendSearch\Lucene\Search\Query\Term(new \ZendSearch\Lucene\Index\Term(self::DOCUMENT_VISIBILITY_PUBLIC, 'visibility')), true);
                $guestContentQuery->addSubquery(new \ZendSearch\Lucene\Search\Query\Term(new \ZendSearch\Lucene\Index\Term(self::DOCUMENT_TYPE_CONTENT, 'type')), true);
                $guestContentQuery->addSubquery(new \ZendSearch\Lucene\Search\Query\Term(new \ZendSearch\Lucene\Index\Term(Space::className(), 'containerModel')), true);
                $guestSpaceListQuery = new \ZendSearch\Lucene\Search\Query\MultiTerm();
                foreach (Space::find()->where(['visibility' => Space::VISIBILITY_ALL])->all() as $space) {
                    $guestSpaceListQuery->addTerm(new \ZendSearch\Lucene\Index\Term($space->id, 'containerPk'));
                }
                $guestContentQuery->addSubquery($guestSpaceListQuery, true);
                $permissionQuery->addSubquery($guestContentQuery);

                // Guest Spaces
                $guestSpacesQuery = new \ZendSearch\Lucene\Search\Query\Boolean();
                $guestSpacesQuery->addSubquery(new \ZendSearch\Lucene\Search\Query\Term(new \ZendSearch\Lucene\Index\Term(self::DOCUMENT_TYPE_SPACE, 'type')), true);
                $guestSpacesQuery->addSubquery(new \ZendSearch\Lucene\Search\Query\Term(new \ZendSearch\Lucene\Index\Term(self::DOCUMENT_VISIBILITY_PUBLIC, 'visibility')), true);
                $permissionQuery->addSubquery($guestSpacesQuery);

                $permissionQuery->addSubquery(new \ZendSearch\Lucene\Search\Query\Term(new \ZendSearch\Lucene\Index\Term(self::DOCUMENT_TYPE_USER, 'type')));
            } else {
                //--- Public Content
                $permissionQuery->addSubquery(new \ZendSearch\Lucene\Search\Query\Term(new \ZendSearch\Lucene\Index\Term(self::DOCUMENT_VISIBILITY_PUBLIC, 'visibility')));

                //--- Private Space Content
                $privateSpaceContentQuery = new \ZendSearch\Lucene\Search\Query\Boolean();
                $privateSpaceContentQuery->addSubquery(new \ZendSearch\Lucene\Search\Query\Term(new \ZendSearch\Lucene\Index\Term(self::DOCUMENT_VISIBILITY_PRIVATE, 'visibility')), true);
                $privateSpaceContentQuery->addSubquery(new \ZendSearch\Lucene\Search\Query\Term(new \ZendSearch\Lucene\Index\Term(Space::className(), 'containerModel')), true);
                $privateSpacesListQuery = new \ZendSearch\Lucene\Search\Query\MultiTerm();

                foreach (\humhub\modules\space\models\Membership::GetUserSpaces() as $space) {
                    $privateSpacesListQuery->addTerm(new \ZendSearch\Lucene\Index\Term($space->id, 'containerPk'));
                }

                $privateSpaceContentQuery->addSubquery($privateSpacesListQuery, true);
                $permissionQuery->addSubquery($privateSpaceContentQuery);
            }
            $query->addSubquery($permissionQuery, true);
        }

        if (count($options['limitSpaces']) > 0) {

            $spaceBaseQuery = new \ZendSearch\Lucene\Search\Query\Boolean();
            $spaceBaseQuery->addSubquery(new \ZendSearch\Lucene\Search\Query\Term(new \ZendSearch\Lucene\Index\Term(Space::className(), 'containerModel')), true);
            $spaceIdQuery = new \ZendSearch\Lucene\Search\Query\MultiTerm();
            foreach ($options['limitSpaces'] as $space) {
                $spaceIdQuery->addTerm(new \ZendSearch\Lucene\Index\Term($space->id, 'containerPk'));
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

        \ZendSearch\Lucene\Search\QueryParser::setDefaultEncoding('utf-8');
        \ZendSearch\Lucene\Analysis\Analyzer\Analyzer::setDefault(new \ZendSearch\Lucene\Analysis\Analyzer\Common\Utf8Num\CaseInsensitive());
        \ZendSearch\Lucene\Search\QueryParser::setDefaultOperator(\ZendSearch\Lucene\Search\QueryParser::B_AND);

        try {
            $index = \ZendSearch\Lucene\Lucene::open($this->getIndexPath());
        } catch (\ZendSearch\Lucene\Exception\RuntimeException $ex) {
            $index = \ZendSearch\Lucene\Lucene::create($this->getIndexPath());
        }

        $this->index = $index;
        return $index;
    }

    protected function getIndexPath()
    {
        $path = Yii::getAlias(Yii::$app->params['search']['zendLucenceDataDir']);

        if (!is_dir($path)) {
            mkdir($path);
        }

        return $path;
    }

}
