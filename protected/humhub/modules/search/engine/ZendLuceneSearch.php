<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2017 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\engine;

use humhub\modules\content\components\ContentActiveRecord;
use humhub\modules\search\commands\SearchController;
use humhub\modules\search\interfaces\Searchable;
use humhub\modules\search\libs\SearchResult;
use humhub\modules\search\libs\SearchResultSet;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use Yii;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Analysis\Analyzer\Common\Utf8Num\CaseInsensitive;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Document\Field;
use ZendSearch\Lucene\Exception\RuntimeException;
use ZendSearch\Lucene\Index\Term;
use ZendSearch\Lucene\Lucene;
use ZendSearch\Lucene\Search\Query\Boolean;
use ZendSearch\Lucene\Search\Query\MultiTerm;
use ZendSearch\Lucene\Search\Query\Term as QueryTerm;
use ZendSearch\Lucene\Search\Query\Wildcard;
use ZendSearch\Lucene\Search\QueryParser;

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

    /**
     * @var integer sets the `termsPerQueryLimit` property for the lucene index.
     * This limits the number of terms in a search query, which also results in a
     * limitation of the number of items a search term can match.
     *
     * This property should be at least as high as the number of items a search can match.
     * It needs to be configured dependent on the amount of items stored in the
     * Humhub database.
     *
     * It can be set to 0 for no limitation, but that may result in search queries
     * to fail caused by high memory usage.
     *
     * Defaults to 2048, which is twice as high as the default value set by Lucene.
     *
     * @see Lucene::getTermsPerQueryLimit()
     */
    public $searchItemLimit = 2048;

    /**
     * @inheritdoc
     */
    public function add(Searchable $obj)
    {
        // Get Primary Key
        $attributes = $obj->getSearchAttributes();

        if ($obj instanceof ContentActiveRecord) {
            $attributes[] = implode(' ', $obj->content->getTags()->select('name')->column());
        }

        $index = $this->getIndex();

        $doc = new Document();

        // Add Meta Data fields
        foreach ($this->getMetaInfoArray($obj) as $fieldName => $fieldValue) {

            if ($fieldName === 'contentTags') {
                // ContentTags needs to be tokenized
                // TODO: Find better approch for meta info field types
                $doc->addField(Field::Text($fieldName, $fieldValue));
            } else {
                $doc->addField(Field::keyword($fieldName, $fieldValue));
            }
        }


        // Add provided search infos
        foreach ($attributes as $key => $val) {
            if (is_array($val)) {
                $val = implode(' ', $val);
            }

            $doc->addField(Field::Text($key, $val, 'UTF-8'));
        }

        foreach ($this->getAdditionalAttributes($obj) as $attrName => $attrValue) {
            if (!empty($attrValue)) {
                $doc->addField(Field::unStored($attrName, VarDumper::dumpAsString($attrValue), 'UTF-8'));
            }
        }

        if (Yii::$app->request->isConsoleRequest && Yii::$app->controller instanceof SearchController) {
            print ".";
        }

        try {
            $index->addDocument($doc);
            $index->commit();
        } catch (RuntimeException $e) {
            Yii::error('Could not add document to search index. Error: ' . $e->getMessage(), 'search');
        }
    }

    public function update(Searchable $object)
    {
        $this->delete($object);
        $this->add($object);
    }

    public function delete(Searchable $obj)
    {
        $this->deleteRecord($obj->className(), $obj->getPrimaryKey());
    }


    public function deleteRecord($className, $primaryKey)
    {
        $index = $this->getIndex();

        $query = new MultiTerm();
        $query->addTerm(new Term($className, 'model'), true);
        $query->addTerm(new Term($primaryKey, 'pk'), true);

        $hits = $index->find($query);
        foreach ($hits as $hit) {
            try {
                $index->delete($hit->id);
            } catch (RuntimeException $e) {
                Yii::error('Could not delete document from search index. Error: ' . $e->getMessage(), 'search');
            }
        }

        try {
            $index->commit();
        } catch (RuntimeException $e) {
            Yii::error('Could not commit search index. Error: ' . $e->getMessage(), 'search');
        }
    }

    public function flush()
    {
        $indexPath = $this->getIndexPath();
        foreach (new \DirectoryIterator($indexPath) as $fileInfo) {
            if ($fileInfo->isDot())
                continue;
            FileHelper::unlink($indexPath . DIRECTORY_SEPARATOR . $fileInfo->getFilename());
        }

        $this->index = null;
    }

    public function find($keyword, array $options)
    {
        $options = $this->setDefaultFindOptions($options);

        $index = $this->getIndex();
        $keyword = str_replace(['*', '?', '_', '$', '-', '.', '\'', '+', '&&', '||', '!', '(', ')', '{', '}', '[', ']', '^', '"', '~', ':', '\\'], ' ', mb_strtolower($keyword, 'utf-8'));

        $query = $this->buildQuery($keyword, $options);
        if ($query === null) {
            return new SearchResultSet();
        }

        if (!isset($options['sortField']) || $options['sortField'] == '') {
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
        Wildcard::setMinPrefixLength(0);

        $query = new Boolean();

        $emptyQuery = true;
        foreach (explode(' ', $keyword) as $k) {
            // Require a minimum of non-wildcard characters
            if (mb_strlen($k, Yii::$app->charset) >= $this->minQueryTokenLength) {
                $term = new Term("*$k*");
                $query->addSubquery(new Wildcard($term), true);
                $emptyQuery = false;
            }
        }

        // if only too short keywords are given, the result is empty
        // when no keyword was given - show some results
        if ($emptyQuery && $keyword != '') {
            return null;
        }

        // Add model filter
        if (isset($options['model']) && $options['model'] != '') {
            if (is_array($options['model'])) {
                $boolQuery = new MultiTerm();
                foreach ($options['model'] as $model) {
                    $boolQuery->addTerm(new Term($model, 'model'));
                }
                $query->addSubquery($boolQuery, true);
            } else {
                $term = new Term($options['model'], 'model');
                $query->addSubquery(new QueryTerm($term), true);
            }
        }

        // Add type filter
        if (isset($options['type']) && $options['type'] != '') {
            if (is_array($options['type'])) {
                $boolQuery = new MultiTerm();
                foreach ($options['type'] as $model) {
                    $boolQuery->addTerm(new Term($type), 'type');
                }
                $query->addSubquery($boolQuery, true);
            } else {
                $term = new Term($options['type'], 'type');
                $query->addSubquery(new QueryTerm($term), true);
            }
        }

        // Add custom filters
        if (isset($options['filters']) && is_array($options['filters'])) {
            foreach ($options['filters'] as $field => $value) {
                $term = new Term($value, $field);
                $query->addSubquery(new QueryTerm($term), true);
            }
        }


        if ($options['checkPermissions'] && !Yii::$app->request->isConsoleRequest) {

            $permissionQuery = new Boolean();

            if (Yii::$app->user->isGuest) {

                // Guest Content
                $guestContentQuery = new Boolean();
                $guestContentQuery->addSubquery(new QueryTerm(new Term(self::DOCUMENT_VISIBILITY_PUBLIC, 'visibility')), true);
                $guestContentQuery->addSubquery(new QueryTerm(new Term(self::DOCUMENT_TYPE_CONTENT, 'type')), true);
                $guestContentQuery->addSubquery(new QueryTerm(new Term(Space::class, 'containerModel')), true);
                $guestSpaceListQuery = new MultiTerm();
                foreach (Space::find()->where(['visibility' => Space::VISIBILITY_ALL])->all() as $space) {
                    $guestSpaceListQuery->addTerm(new Term($space->id, 'containerPk'));
                }
                $guestContentQuery->addSubquery($guestSpaceListQuery, true);
                $permissionQuery->addSubquery($guestContentQuery);

                // Guest Spaces
                $guestSpacesQuery = new Boolean();
                $guestSpacesQuery->addSubquery(new QueryTerm(new Term(self::DOCUMENT_TYPE_SPACE, 'type')), true);
                $guestSpacesQuery->addSubquery(new QueryTerm(new Term(self::DOCUMENT_VISIBILITY_PUBLIC, 'visibility')), true);
                $permissionQuery->addSubquery($guestSpacesQuery);

                $permissionQuery->addSubquery(new QueryTerm(new Term(self::DOCUMENT_TYPE_USER, 'type')));
            } else {
                //--- Public Content
                $permissionQuery->addSubquery(new QueryTerm(new Term(self::DOCUMENT_VISIBILITY_PUBLIC, 'visibility')));

                //--- Private Space Content
                $privateSpaceContentQuery = new Boolean();
                $privateSpaceContentQuery->addSubquery(new QueryTerm(new Term(self::DOCUMENT_VISIBILITY_PRIVATE, 'visibility')), true);
                $privateSpaceContentQuery->addSubquery(new QueryTerm(new Term(Space::class, 'containerModel')), true);
                $privateSpacesListQuery = new MultiTerm();

                foreach (Membership::getUserSpaceIds() as $spaceId) {
                    $privateSpacesListQuery->addTerm(new Term($spaceId, 'containerPk'));
                }

                $privateSpaceContentQuery->addSubquery($privateSpacesListQuery, true);
                $permissionQuery->addSubquery($privateSpaceContentQuery);
            }
            $query->addSubquery($permissionQuery, true);
        }

        if (count($options['limitSpaces']) > 0) {

            $spaceBaseQuery = new Boolean();
            $spaceBaseQuery->addSubquery(new QueryTerm(new Term(Space::class, 'containerModel')), true);
            $spaceIdQuery = new MultiTerm();
            foreach ($options['limitSpaces'] as $space) {
                $spaceIdQuery->addTerm(new Term($space->id, 'containerPk'));
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

        if ($this->index != null) {
            return $this->index;
        }

        QueryParser::setDefaultEncoding('utf-8');
        Analyzer::setDefault(new CaseInsensitive());
        QueryParser::setDefaultOperator(QueryParser::B_AND);

        Lucene::setTermsPerQueryLimit($this->searchItemLimit);

        try {
            $index = Lucene::open($this->getIndexPath());
        } catch (RuntimeException $ex) {
            $index = Lucene::create($this->getIndexPath());
        }

        $this->index = $index;
        return $index;
    }

    protected function getIndexPath()
    {
        $path = Yii::getAlias(Yii::$app->params['search']['zendLucenceDataDir']);
        FileHelper::createDirectory($path);

        return $path;
    }

}
