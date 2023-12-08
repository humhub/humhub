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
use ZendSearch\Lucene\Index\Term;
use ZendSearch\Lucene\Lucene;
use ZendSearch\Lucene\Search\Query\Boolean;
use ZendSearch\Lucene\Search\Query\Term as QueryTerm;
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
            if ($fileInfo->isDot())
                continue;
            FileHelper::unlink($indexPath . DIRECTORY_SEPARATOR . $fileInfo->getFilename());
        }

        $this->_index = null;
    }

    public function update(Content $content): void
    {
        $document = new Document();
        $document->addField(Field::keyword('content.id', $content->id));
        $document->addField(Field::keyword('content.visibility', $content->visibility));
        $document->addField(Field::keyword('content.class', $content->object_model));
        $document->addField(Field::unStored('content.tags', implode(', ',
            array_map(function (ContentTag $tag) {
                return $tag->name;
            }, $content->tags))));
        $document->addField(Field::keyword('content.container_id', $content->container->id));
        $document->addField(Field::keyword('content.created_at', strtotime($content->created_at)));
        $document->addField(Field::keyword('content.created_by', $content->created_by));
        if ($content->createdBy) {
            $document->addField(Field::unStored('content.created_by_name', $content->createdBy->displayName));
        }

        $document->addField(Field::keyword('content.updated_at', strtotime($content->created_at)));
        if ($content->updatedBy) {
            //$document->addField(Field::keyword('content.updated_by', $content->updatedBy->getDisplayName()));
        }
        $document->addField(Field::unStored('content.comments', (new ContentSearchService($content))->getCommentsAsText()));
        $document->addField(Field::unStored('content.files', (new ContentSearchService($content))->getFileContentAsText()));

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
        $query = new TermQuery(new Term($content->id, 'id'));
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
        $query = new Boolean();
        foreach ($request->getKeywords() as $keyword) {
            if (mb_strlen($keyword) < 3) {
                $query->addSubquery(new \ZendSearch\Lucene\Search\Query\Term(new Term(mb_strtolower($keyword))), true);
            } else {
                $query->addSubquery(new \ZendSearch\Lucene\Search\Query\Wildcard(new Term(mb_strtolower($keyword))), true);
            }
        }

        if (!empty($request->contentType)) {
            $query->addSubquery(new QueryTerm(new Term($request->contentType, 'content.class')), true);
        }

        if ($request->author) {
            $query->addSubquery(new QueryTerm(new Term($request->author->id, 'content.created_by')), true);
        }

        if ($request->user !== null) {
            //$this->addQueryFilterUser($query, $options->contentTypes);
        }
        if ($request->contentContainer !== null) {
            //$this->addQueryFilterContentContainer($query, $options->contentTypes);
        }

        $hits = new ArrayObject($this->getIndex()->find($query, $request->orderBy));

        $resultSet = new ResultSet();
        $resultSet->pagination = new Pagination();
        $resultSet->pagination->totalCount = count($hits);
        $resultSet->pagination->pageSize = $request->pageSize;

        $hits = new \LimitIterator(
            $hits->getIterator(),
            $resultSet->pagination->page * $resultSet->pagination->pageSize,
            $resultSet->pagination->pageSize
        );

        foreach ($hits as $hit) {
            try {
                $contentId = $hit->getDocument()->getField('content.id')->getUtf8Value();
            } catch (\Exception $ex) {
                throw new \Exception('Could not get content id from Lucence search result');
            }
            $content = Content::findOne(['id' => $contentId]);
            if ($content !== null) {
                $resultSet->results[] = $content;
            } else {
                throw new Exception('Could not load result!');
                // ToDo: Delete Result
                Yii::error("Could not load search result content: " . $contentId);
            }
        }

        return $resultSet;
    }

    private function getIndex()
    {
        if ($this->_index) {
            return $this->_index;
        }

        QueryParser::setDefaultEncoding('utf-8');
        Analyzer::setDefault(new CaseInsensitive());
        QueryParser::setDefaultOperator(QueryParser::B_AND);
        Lucene::setTermsPerQueryLimit(10000);

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
