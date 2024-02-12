<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

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
        $query->andWhere('content_fulltext.content_id IS NOT NULL');

        $againstSqlQuery = '';
        foreach ($request->getKeywords() as $keyword) {
            $againstSqlQuery .= '+' . $keyword . ' ';
        }

        $matchDbFields = 'content_fulltext.contents, content_fulltext.comments, content_fulltext.files';

        $query->addSelect(['*', 'MATCH(' . $matchDbFields . ') AGAINST ("' . Yii::$app->db->quoteValue($againstSqlQuery) . '" IN BOOLEAN MODE) as score']);
        $query->andWhere('MATCH(' . $matchDbFields . ') AGAINST (:key IN BOOLEAN MODE)', ['key' => $againstSqlQuery]);

        if (!empty($request->contentType)) {
            $query->andWhere(['content.object_model' => $request->contentType]);
        }

        if (!empty($request->dateFrom)) {
            $query->andWhere(['>=', 'content.created_at', $request->dateFrom . ' 00:00:00']);
        }
        if (!empty($request->dateTo)) {
            $query->andWhere(['<=', 'content.created_at', $request->dateTo . ' 23:59:59']);
        }

        if (!empty($request->topic)) {
            $query->leftJoin('content_tag_relation', 'content_tag_relation.content_id = content.id')
                ->andWhere(['IN', 'content_tag_relation.tag_id', $request->topic]);
        }

        if (!empty($request->author)) {
            $query->leftJoin('user', 'user.id = content.created_by')
                ->andWhere(['IN', 'user.guid', $request->author]);
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
