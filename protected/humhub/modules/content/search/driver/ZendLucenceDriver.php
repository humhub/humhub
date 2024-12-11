<?php

namespace humhub\modules\content\search\driver;

use ArrayObject;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentTag;
use humhub\modules\content\search\ResultSet;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\content\services\ContentSearchService;
use humhub\modules\content\widgets\richtext\converter\RichTextToPlainTextConverter;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\User;
use Yii;
use yii\base\InvalidConfigException;
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
use ZendSearch\Lucene\Search\Query\AbstractQuery;
use ZendSearch\Lucene\Search\Query\Boolean;
use ZendSearch\Lucene\Search\Query\MultiTerm;
use ZendSearch\Lucene\Search\Query\Phrase;
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

        $unStoredFields = ['comments', 'files'];
        foreach ($this->getFields($content) as $field => $value) {
            if (in_array($field, $unStoredFields)) {
                $document->addField(Field::unStored($field, $value));
            } else {
                $document->addField(Field::keyword($field, $value));
            }
        }

        foreach ($content->getModel()->getSearchAttributes() as $attributeName => $attributeValue) {
            $document->addField(Field::unStored($attributeName, RichTextToPlainTextConverter::process($attributeValue)));
        }

        try {
            $this->getIndex()->addDocument($document);
            $this->commit();
        } catch (RuntimeException $e) {
            Yii::error('Could not add document to search index. Error: ' . $e->getMessage(), 'search');
        }
    }

    protected function getFields(Content $content): array
    {
        return [
            'content_id' => $content->id,
            'visibility' => $content->visibility === Content::VISIBILITY_PUBLIC ? Content::VISIBILITY_PUBLIC : Content::VISIBILITY_PRIVATE,
            'class' => $content->object_model,
            'created_at' => $content->created_at,
            'created_by' => ($author = $content->createdBy) ? $author->guid : '',
            'updated_at' => $content->updated_at,
            'updated_by' => ($author = $content->updatedBy) ? $author->guid : '',
            'tags' => empty($content->tags) ? ''
                : '-' . implode('-', array_map(function (ContentTag $tag) {
                    return $tag->id;
                }, $content->tags)) . '-',
            'container_guid' => ($container = $content->container) ? $container->guid : '',
            'container_visibility' => $container ? $container->visibility : '',
            'container_class' => $container ? get_class($container) : '',
            'comments' => (new ContentSearchService($content))->getCommentsAsText(),
            'files' => (new ContentSearchService($content))->getFileContentAsText(),
        ];
    }

    public function delete(int $contentId): void
    {
        $query = new TermQuery(new Term($contentId, 'content_id'));
        foreach ($this->getIndex()->find($query) as $result) {
            try {
                $this->getIndex()->delete($result->id);
            } catch (RuntimeException $e) {
                Yii::error('Could not delete document from search index. Error: ' . $e->getMessage(), 'content');
            }
        }
        $this->commit();
    }

    /**
     * @inheritdoc
     */
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
            $resultSet->pagination->pageSize,
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
                Yii::warning("Deleted non-existing content from search index. Content ID: " . $contentId, 'content');
                $this->delete($contentId);
            }
        }

        return $resultSet;
    }

    protected function prepareTerm(string $term): AbstractQuery
    {
        $term = mb_strtolower($term);

        if (str_contains($term, ' ')) {
            return new Phrase(explode(' ', $term));
        }

        if (str_ends_with($term, '*')) {
            return new Wildcard(new Term($term));
        }

        return new TermQuery(new Term($term));
    }

    protected function buildSearchQuery(SearchRequest $request): Boolean
    {
        $query = new Boolean();

        Wildcard::setMinPrefixLength(0);

        $keywordQuery = new Boolean();

        foreach ($request->getSearchQuery()->terms as $term) {
            $keywordQuery->addSubquery($this->prepareTerm($term), true);
        }

        foreach ($request->getSearchQuery()->notTerms as $term) {
            $keywordQuery->addSubquery($this->prepareTerm($term), false);
        }

        if (count($keywordQuery->getSubqueries())) {
            $query->addSubquery($keywordQuery, true);
        }

        if (!empty($request->dateFrom) || !empty($request->dateTo)) {
            $dateFrom = $this->convertRangeValue('created_at', $request->dateFrom, ' 00:00:00');
            $dateTo = $this->convertRangeValue('created_at', $request->dateTo, ' 23:59:59');
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

        if (!empty($request->contentContainerClass)) {
            $query->addSubquery(new TermQuery(new Term($request->contentContainerClass, 'container_class')), true);
        }

        if ($request->contentContainer) {
            $containers = [];
            $signs = [];
            foreach ($request->contentContainer as $contentContainerGuid) {
                $containers[] = new Term($contentContainerGuid, 'container_guid');
                $signs[] = null;
            }
            $query->addSubquery(new MultiTerm($containers, $signs), true);
        }

        if (!empty($request->contentType)) {
            $query->addSubquery(new TermQuery(new Term($request->contentType, 'class')), true);
        }

        return $this->addQueryFilterVisibility($query);
    }

    /**
     * @throws \Throwable
     * @throws InvalidConfigException
     */
    protected function addQueryFilterVisibility(Boolean $query): Boolean
    {
        $permissionQuery = new Boolean();

        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();

            // Public Content
            $permissionQuery->addSubquery(new TermQuery(new Term(Content::VISIBILITY_PUBLIC, 'visibility')));

            // Own created content is always visible
            $permissionQuery->addSubquery(new TermQuery(new Term($user->guid ?? null, 'created_by')));

            if ($user?->canManageAllContent()) {
                // Don't restrict if user can view all Space content:
                $permissionQuery->addSubquery(new TermQuery(new Term(Space::class, 'container_class')));
            } else {
                // Private Space Content
                $privateSpaceContentQuery = new Boolean();
                $privateSpaceContentQuery->addSubquery(new TermQuery(new Term(Content::VISIBILITY_PRIVATE, 'visibility')), true);
                $privateSpaceContentQuery->addSubquery(new TermQuery(new Term(Space::class, 'container_class')), true);

                $privateSpacesListQuery = new MultiTerm();
                $membershipSpaces = Membership::getUserSpaces();
                if (empty($membershipSpaces)) {
                    $privateSpacesListQuery->addTerm(new Term('no-membership-spaces', 'container_guid'));
                } else {
                    foreach ($membershipSpaces as $space) {
                        $privateSpacesListQuery->addTerm(new Term($space->guid, 'container_guid'));
                    }
                }
                $privateSpaceContentQuery->addSubquery($privateSpacesListQuery, true);

                $permissionQuery->addSubquery($privateSpaceContentQuery);
            }

            if ($user?->canManageAllContent()) {
                // Don't restrict if user can view all User content:
                $permissionQuery->addSubquery(new TermQuery(new Term(User::class, 'container_class')));
            } else {
                // Private User Content
                $privateUserContentQuery = new Boolean();
                $privateUserContentQuery->addSubquery(new TermQuery(new Term(Content::VISIBILITY_PRIVATE, 'visibility')), true);
                $privateUserContentQuery->addSubquery(new TermQuery(new Term(User::class, 'container_class')), true);
                $privateUserContentQuery->addSubquery(new TermQuery(new Term($user->guid ?? null, 'container_guid')), true);
                $permissionQuery->addSubquery($privateUserContentQuery);
            }
        } elseif (AuthHelper::isGuestAccessEnabled()) {
            // Guest Content
            $guestContentQuery = new Boolean();
            $guestContentQuery->addSubquery(new TermQuery(new Term(Content::VISIBILITY_PUBLIC, 'visibility')), true);
            $guestContentQuery->addSubquery(new TermQuery(new Term(Space::class, 'container_class')), true);
            $guestSpaceListQuery = new MultiTerm();
            foreach (Space::findAll(['visibility' => Space::VISIBILITY_ALL]) as $space) {
                $guestSpaceListQuery->addTerm(new Term($space->guid, 'container_guid'));
            }
            $guestContentQuery->addSubquery($guestSpaceListQuery, true);
            $permissionQuery->addSubquery($guestContentQuery);
        } else {
            // Exclude all contents from searching when guest access is disabled
            $permissionQuery->addSubquery(new TermQuery(new Term('Denied', 'visibility')));
        }

        $query->addSubquery($permissionQuery, true);

        return $query;
    }

    /**
     * ZendLucene and Solr sometimes require a different format here.
     * e.g. Sol needs a "*" instead of "null" and quoted dates with time
     *
     * @param string $field
     * @param string|null $value
     * @param string $suffix
     * @return Term|null
     */
    protected function convertRangeValue(string $field, ?string $value, string $suffix = ''): ?Term
    {
        return empty($value) ? null : new Term($value . $suffix, $field);
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
