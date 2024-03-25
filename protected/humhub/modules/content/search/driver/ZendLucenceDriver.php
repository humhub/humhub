<?php

namespace humhub\modules\content\search\driver;

use ArrayObject;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentTag;
use humhub\modules\content\search\ResultSet;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\content\services\ContentSearchService;
use Yii;
use yii\base\Exception;
use yii\data\Pagination;
use yii\helpers\FileHelper;
use ZendSearch\Lucene\Analysis\Analyzer\Analyzer;
use ZendSearch\Lucene\Analysis\Analyzer\Common\Utf8Num\CaseInsensitive;
use ZendSearch\Lucene\Document;
use ZendSearch\Lucene\Document\Field;
use ZendSearch\Lucene\Exception\RuntimeException;
use ZendSearch\Lucene\Index;
use ZendSearch\Lucene\Index\Term;
use ZendSearch\Lucene\Lucene;
use ZendSearch\Lucene\Search\Query\Boolean;
use ZendSearch\Lucene\Search\Query\MultiTerm;
use ZendSearch\Lucene\Search\Query\Range;
use ZendSearch\Lucene\Search\Query\Term as TermQuery;
use ZendSearch\Lucene\Search\Query\Wildcard;
use ZendSearch\Lucene\Search\QueryParser;
use ZendSearch\Lucene\SearchIndexInterface;

class ZendLucenceDriver extends AbstractDriver
{
    private ?SearchIndexInterface $_index = null;

    public function purge(): void
    {
        $indexPath = $this->getIndexPath();

        foreach (new \DirectoryIterator($indexPath) as $fileInfo) {
            if ($fileInfo->isDot()) {
                continue;
            }
            FileHelper::unlink($indexPath . DIRECTORY_SEPARATOR . $fileInfo->getFilename());
        }

        $this->_index = null;
    }

    public function update(Content $content): void
    {
        $document = new Document();
        $document->addField(Field::keyword('content_id', $content->id));
        $document->addField(Field::keyword('visibility', $content->visibility));
        $document->addField(Field::keyword('class', $content->object_model));
        $document->addField(Field::keyword('created_at', $content->created_at));
        $document->addField(Field::keyword('created_by', ($author = $content->createdBy) ? $author->guid : ''));
        $document->addField(Field::keyword('updated_at', $content->updated_at));
        $document->addField(Field::keyword('updated_by', ($author = $content->updatedBy) ? $author->guid : ''));
        $document->addField(Field::keyword('space', ($space = $content->container) ? $space->guid : ''));
        $document->addField(Field::keyword('tags', empty($content->tags) ? ''
            : '-' . implode('-', array_map(function (ContentTag $tag) {
                return $tag->id;
            }, $content->tags)) . '-'));

        if ($content->container) {
            $document->addField(Field::keyword('container_id', $content->container->id));
        }

        $document->addField(Field::unStored('comments', (new ContentSearchService($content))->getCommentsAsText()));
        $document->addField(Field::unStored('files', (new ContentSearchService($content))->getFileContentAsText()));

        foreach ($content->getModel()->getSearchAttributes() as $attributeName => $attributeValue) {
            $document->addField(Field::unStored($attributeName, $attributeValue));
        }

        try {
            $this->getIndex()->addDocument($document);
            $this->commit();
        } catch (RuntimeException $e) {
            Yii::error('Could not add document to search index. Error: ' . $e->getMessage(), 'search');
        }
    }

    public function delete(Content $content): void
    {
        $query = new TermQuery(new Term($content->id, 'content_id'));
        foreach ($this->getIndex()->find($query) as $result) {
            try {
                $this->getIndex()->delete($result->id);
            } catch (RuntimeException $e) {
                Yii::error('Could not delete document from search index. Error: ' . $e->getMessage(), 'content');
            }
        }
        $this->commit();
    }

    public function search(SearchRequest $request): ResultSet
    {
        $query = $this->buildSearchQuery($request);

        if ($request->orderBy === SearchRequest::ORDER_BY_CREATION_DATE) {
            $hits = new ArrayObject($this->getIndex()->find($query, 'created_at', SORT_DESC));
        } else {
            $hits = new ArrayObject($this->getIndex()->find($query));
        }

        $resultSet = new ResultSet();
        $resultSet->pagination = new Pagination();
        $resultSet->pagination->totalCount = count($hits);
        $resultSet->pagination->pageSize = $request->pageSize;
        $resultSet->pagination->setPage($request->page - 1, true);

        $hits = new \LimitIterator(
            $hits->getIterator(),
            $resultSet->pagination->page * $resultSet->pagination->pageSize,
            $resultSet->pagination->pageSize
        );


        foreach ($hits as $hit) {
            try {
                $contentId = $hit->getDocument()->getField('content_id')->getUtf8Value();
            } catch (\Exception $ex) {
                throw new \Exception('Could not get content id from Lucence search result');
            }
            $content = Content::findOne(['id' => $contentId]);
            if ($content !== null) {
                $resultSet->results[] = $content;
            } else {
                throw new Exception('Could not load result! Content ID: ' . $contentId);
                // ToDo: Delete Result
                Yii::error("Could not load search result content: " . $contentId);
            }
        }

        return $resultSet;
    }

    protected function buildSearchQuery(SearchRequest $request): Boolean
    {
        $query = new Boolean();

        Wildcard::setMinPrefixLength(0);

        $keywordQuery = new Boolean();
        foreach ($request->getSearchQuery()->orTerms as $term) {
            $keywordQuery->addSubquery(new Wildcard(new Term(mb_strtolower($term) . '*')), null);
        }

        foreach ($request->getSearchQuery()->andTerms as $term) {
            $keywordQuery->addSubquery(new Wildcard(new Term(mb_strtolower($term) . '*')), true);
        }

        foreach ($request->getSearchQuery()->notTerms as $term) {
            $keywordQuery->addSubquery(new TermQuery(new Term(mb_strtolower($term))), false);
        }

        if (count($keywordQuery->getSubqueries())) {
            $query->addSubquery($keywordQuery, true);
        }

        if (!empty($request->dateFrom) || !empty($request->dateTo)) {
            $dateFrom = empty($request->dateFrom)
                ? null
                : new Term($request->dateFrom . ' 00:00:00', 'created_at');
            $dateTo = empty($request->dateTo)
                ? null
                : new Term($request->dateTo . ' 23:59:59', 'created_at');
            $query->addSubquery(new Range($dateFrom, $dateTo, true), true);
        }

        if (!empty($request->topic)) {
            $subQuery = new Boolean();
            foreach ($request->topic as $topic) {
                $subQuery->addSubquery(new Wildcard(new Term('*-' . $topic . '-*', 'tags')));
            }
            $query->addSubquery($subQuery, true);
        }

        if ($request->author) {
            $authors = [];
            $signs = [];
            foreach ($request->author as $author) {
                $authors[] = new Term($author, 'created_by');
                $signs[] = null;
            }
            $query->addSubquery(new MultiTerm($authors, $signs), true);
        }

        if ($request->space) {
            $spaces = [];
            $signs = [];
            foreach ($request->space as $space) {
                $spaces[] = new Term($space, 'space');
                $signs[] = null;
            }
            $query->addSubquery(new MultiTerm($spaces, $signs), true);
        }

        if (!empty($request->contentType)) {
            $query->addSubquery(new TermQuery(new Term($request->contentType, 'class')), true);
        }

        return $query;
    }

    private function getIndex(): Index
    {
        if ($this->_index) {
            return $this->_index;
        }

        QueryParser::setDefaultEncoding('utf-8');
        Analyzer::setDefault(new CaseInsensitive());
        QueryParser::setDefaultOperator(QueryParser::B_AND);
        Lucene::setTermsPerQueryLimit(1024 * 1024);

        try {
            $this->_index = Lucene::open($this->getIndexPath());
        } catch (RuntimeException $ex) {
            $this->_index = Lucene::create($this->getIndexPath());
        }

        return $this->_index;
    }

    private function getIndexPath()
    {
        $path = Yii::getAlias('@runtime/content-search-db');
        FileHelper::createDirectory($path);

        return $path;
    }

    private function commit()
    {
        try {
            $this->getIndex()->commit();
        } catch (RuntimeException $e) {
            Yii::error('Could not commit search index. Error: ' . $e->getMessage(), 'search');
        }
    }
}
