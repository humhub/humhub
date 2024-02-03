<?php

namespace humhub\modules\content\search\driver;

use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentFulltext;
use humhub\modules\content\models\ContentTag;
use humhub\modules\content\search\ResultSet;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\content\services\ContentSearchService;
use Yii;
use yii\data\Pagination;

class MysqlDriver extends AbstractDriver
{
    public function purge(): void
    {
        ContentFulltext::deleteAll();
    }

    public function update(Content $content): void
    {
        $this->delete($content);

        $record = new ContentFulltext();
        $record->content_id = $content->id;

        $record->contents = implode(', ',
                array_map(function (ContentTag $tag) {
                    return $tag->name;
                }, $content->tags)) . " \n";

        $record->contents .= $content->createdBy->displayName . " \n";

        foreach ($content->getModel()->getSearchAttributes() as $attributeName => $attributeValue) {
            $record->contents .= $attributeValue . " \n";
        }

        $record->comments .= (new ContentSearchService($content))->getCommentsAsText() . " \n";
        $record->files .= (new ContentSearchService($content))->getFileContentAsText() . " \n";

        $record->save();
    }

    public function delete(Content $content): void
    {
        ContentFulltext::deleteAll(['content_id' => $content->id]);
    }

    public function search(SearchRequest $request): ResultSet
    {
        $query = Content::find();
        $query->leftJoin('content_fulltext', 'content_fulltext.content_id=content.id');

        $fields = 'content_fulltext.contents, content_fulltext.comments, content_fulltext.files';

        $query->addSelect(['*', 'MATCH(' . $fields . ') AGAINST ("' . Yii::$app->db->quoteValue($request->keyword) . '" IN NATURAL LANGUAGE MODE) as score']);
        $query->where('MATCH(' . $fields . ') AGAINST (:key IN NATURAL LANGUAGE MODE)', ['key' => $request->keyword]);

        if (!empty($request->contentType)) {
            $query->andWhere(['content.object_model' => $request->contentType]);
        }

        if ($request->author) {
            $query->andWhere(['content.created_by' => $request->author->id]);
        }

        if ($request->user !== null) {
            //$this->addQueryFilterUser($query, $options->contentTypes);
        }
        if ($request->contentContainer !== null) {
            //$this->addQueryFilterContentContainer($query, $options->contentTypes);
        }

        if ($request->orderBy === SearchRequest::ORDER_BY_CREATION_DATE) {
            $query->orderBy(['content.created_at' => SORT_DESC]);
        } else {
            $query->orderBy(['score' => SORT_DESC]);
        }

        $resultSet = new ResultSet();
        $resultSet->pagination = new Pagination();
        $resultSet->pagination->totalCount = $query->count();
        $resultSet->pagination->pageSize = $request->pageSize;

        $query->offset($resultSet->pagination->offset)->limit($resultSet->pagination->limit);

        foreach ($query->all() as $content) {
            $resultSet->results[] = $content;
        }

        return $resultSet;
    }
}
