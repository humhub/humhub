<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\content\search\driver;

use humhub\libs\SearchQuery;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentFulltext;
use humhub\modules\content\models\ContentTag;
use humhub\modules\content\search\ResultSet;
use humhub\modules\content\search\SearchRequest;
use humhub\modules\content\services\ContentSearchService;
use humhub\modules\content\widgets\richtext\converter\RichTextToPlainTextConverter;
use humhub\modules\space\models\Space;
use humhub\modules\user\helpers\AuthHelper;
use humhub\modules\user\models\User;
use Yii;
use yii\data\Pagination;
use yii\db\ActiveQuery;
use yii\db\Expression;

class MysqlDriver extends AbstractDriver
{
    /**
     * Minimum word length for "And Terms",
     * Words with less length are handled as "Or Terms"
     * NOTE: Using of the config value mysql.ft_min_word_len doesn't work properly.
     *
     * @var int $minAndTermLength
     */
    public int $minAndTermLength = 3;

    public function purge(): void
    {
        ContentFulltext::deleteAll();
    }

    public function update(Content $content): void
    {
        $this->delete($content->id);

        $record = new ContentFulltext();
        $record->content_id = $content->id;
        $record->contents = $content->id;

        $record->contents .= implode(
            ', ',
            array_map(function (ContentTag $tag) {
                return $tag->name;
            }, $content->tags),
        ) . " \n";

        foreach ($content->getModel()->getSearchAttributes() as $attributeValue) {
            $record->contents .= RichTextToPlainTextConverter::process($attributeValue) . " \n";
        }

        $record->comments .= (new ContentSearchService($content))->getCommentsAsText() . " \n";
        $record->files .= (new ContentSearchService($content))->getFileContentAsText() . " \n";

        $record->save();
    }

    public function delete(int $contentId): void
    {
        ContentFulltext::deleteAll(['content_id' => $contentId]);
    }

    /**
     * @inheritdoc
     */
    public function search(SearchRequest $request): ResultSet
    {
        $query = Content::find();
        $query->leftJoin('content_fulltext', 'content_fulltext.content_id=content.id');
        $query->andWhere('content_fulltext.content_id IS NOT NULL');

        $fullTextQuery = $this->createMysqlFullTextQuery($request->getSearchQuery(), [
            'content_fulltext.contents', 'content_fulltext.comments', 'content_fulltext.files',
        ]);

        $query->addSelect(['content.*', $fullTextQuery . ' as score']);
        $query->andWhere($fullTextQuery);

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

        if (!empty($request->contentContainerClass)) {
            $query->andWhere(['contentcontainer.class' => $request->contentContainerClass]);
        }

        if (!empty($request->contentContainer)) {
            $query->andWhere(['IN', 'contentcontainer.guid', $request->contentContainer]);
        }

        $this->addQueryFilterVisibility($query);

        if ($request->orderBy === SearchRequest::ORDER_BY_CREATION_DATE) {
            $query->orderBy(['content.created_at' => SORT_DESC]);
        } else {
            $query->orderBy(['score' => SORT_DESC]);
        }

        $resultSet = new ResultSet();
        $resultSet->pagination = new Pagination();
        $resultSet->pagination->totalCount = $query->count();
        $resultSet->pagination->pageSize = $request->pageSize;
        $resultSet->pagination->setPage($request->page - 1, true);

        $query->offset($resultSet->pagination->offset)->limit($resultSet->pagination->limit);

        foreach ($query->all() as $content) {
            $resultSet->results[] = $content;
        }

        return $resultSet;
    }

    private function createMysqlFullTextQuery(SearchQuery $query, array $matchFields = []): string
    {
        $againstQuery = '';

        foreach ($query->terms as $term) {
            if (strlen(rtrim($term, '*')) >= $this->minAndTermLength) {
                $againstQuery .= '+';// Search with "AND" condition
            }
            $againstQuery .= $this->prepareTerm($term) . ' ';
        }
        foreach ($query->notTerms as $term) {
            $againstQuery .= '-' . $this->prepareTerm($term) . ' ';
        }

        return sprintf(
            'MATCH(%s) AGAINST (%s IN BOOLEAN MODE)',
            implode(', ', $matchFields),
            Yii::$app->db->quoteValue(trim($againstQuery)),
        );
    }

    protected function prepareTerm(string $term): string
    {
        // Remove chars `-` to avoid mysql error
        $term = preg_replace('/-+(\*?)$/', '$1', $term);

        // Wrap a keyword in quotes to avoid error with the special chars in the sql MATCH-AGAINST expression
        return preg_match('#[^\p{L}\d\*’\'`\-\_]#u', $term) ? '"' . $term . '"' : $term;
    }

    protected function addQueryFilterVisibility(ActiveQuery $query): ActiveQuery
    {
        $query->andWhere(['content.state' => Content::STATE_PUBLISHED]);

        $query->joinWith('contentContainer');
        $query->leftJoin('space', 'contentcontainer . pk = space . id and contentcontainer .class=:spaceClass', [':spaceClass' => Space::class]);
        $query->leftJoin('user cuser', 'contentcontainer . pk = cuser . id and contentcontainer .class=:userClass', [':userClass' => User::class]);

        if (!Yii::$app->user->isGuest) {
            $user = Yii::$app->user->getIdentity();

            $query->leftJoin('space_membership', 'contentcontainer . pk = space_membership . space_id and contentcontainer .class=:spaceClass and space_membership . user_id =:userId', [':userId' => $user->id, ':spaceClass' => Space::class]);

            if ($user?->canManageAllContent()) {
                // Don't restrict if user can view all content:
                $conditionSpaceMembershipRestriction = '';
                $conditionUserPrivateRestriction = '';
            } else {
                // User must be a space's member OR Space and Content are public
                $conditionSpaceMembershipRestriction = ' AND ( space_membership.status=3 OR (content.visibility=1 AND space.visibility != 0) )';
                // User can view only content of own profile
                $conditionUserPrivateRestriction = ' AND content.contentcontainer_id=' . $user->contentcontainer_id;
            }

            // Build Access Check based on Space Content Container
            $conditionSpace = 'space.id IS NOT NULL' . $conditionSpaceMembershipRestriction;

            // Build Access Check based on User Content Container
            $conditionUser = 'cuser.id IS NOT NULL AND (';                                         // user content
            $conditionUser .= '   (content.visibility = 1) OR';                                     // public visible content
            $conditionUser .= '   (content.visibility = 0' . $conditionUserPrivateRestriction . ')';  // private content of user
            if (Yii::$app->getModule('friendship')->isFriendshipEnabled()) {
                $query->leftJoin('user_friendship cff', 'cuser.id=cff.user_id AND cff.friend_user_id=:fuid', [':fuid' => $user->id]);
                $conditionUser .= ' OR (content.visibility = 0 AND cff.id IS NOT NULL)';  // users are friends
            }
            $conditionUser .= ')';

            // Created content of is always visible
            $conditionUser .= 'OR content.created_by=' . $user->id;
            $globalCondition = 'content.contentcontainer_id IS NULL';
        } elseif (AuthHelper::isGuestAccessEnabled()) {
            $conditionSpace = 'space.id IS NOT NULL and space.visibility=' . Space::VISIBILITY_ALL . ' AND content.visibility=1';
            $conditionUser = 'cuser.id IS NOT NULL and cuser.visibility=' . User::VISIBILITY_ALL . ' AND content.visibility=1';
            $globalCondition = 'content.contentcontainer_id IS NULL AND content.visibility=1';
        } else {
            // Exclude all contents from searching when guest access is disabled
            return $query->where(new Expression('NULL'));
        }

        $query->andWhere("{$conditionSpace} OR {$conditionUser} OR {$globalCondition}");

        return $query;
    }
}
