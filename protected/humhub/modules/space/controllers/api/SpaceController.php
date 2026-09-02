<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers\api;

use humhub\components\api\BaseController;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\serializers\SpaceSerializer;
use humhub\modules\user\models\Follow;
use Yii;
use yii\db\Expression;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;

/**
 * The space list of the HTTP API (see `docs/develop/concept-api.md`).
 *
 * This is the general list of spaces, not one endpoint per consumer: the space chooser island
 * reads it, and anything else browsing spaces (a picker, a directory) can read the same shape.
 * What it answers is therefore caller-NEUTRAL — no `isMember`, no unread counts. A caller that
 * needs to know which of the listed spaces are its own asks for them (`scope`), and a caller
 * that needs to know what it is to them asks {@see self::actionStates()}. That split keeps one
 * representation for every consumer, and it is the same rule the comment payload follows.
 *
 * Visibility is never optional: every query runs through `visible()` and `filterBlockedSpaces()`,
 * so a space the caller may not see cannot appear no matter which parameters arrive.
 *
 * @since 1.20
 */
class SpaceController extends BaseController
{
    /**
     * @var int the largest page a client may ask for
     */
    public const MAX_PAGE_SIZE = 100;

    /**
     * @var int the most spaces one state request may name — a client asks for the page it
     * displays, not for every space a user is a member of
     */
    public const MAX_STATE_GUIDS = 100;

    /**
     * @var string[] the scopes `scope` accepts
     */
    public const SCOPES = ['all', 'member', 'following', 'mine'];

    /**
     * @inheritdoc
     */
    protected bool $enableSessionAuth = true;

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return ArrayHelper::merge(parent::behaviors(), [
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['GET', 'HEAD'],
                    'states' => ['GET', 'HEAD'],
                ],
            ],
        ]);
    }

    /**
     * The spaces the caller may see.
     *
     * Parameters: `q` (search over name, description and tags), `scope`
     * (`all`, `member`, `following`, `mine` — the caller's memberships and followed spaces),
     * `archived` (`1` to include archived spaces, excluded by default) plus `page`/`pageSize`.
     *
     * A scoped list is ordered the way the platform orders a user's spaces: memberships first,
     * in the order the `spaceOrder` setting asks for, followed spaces after them.
     */
    public function actionIndex()
    {
        $request = Yii::$app->request;
        $user = Yii::$app->user->getIdentity();

        $query = Space::find()->visible($user)->filterBlockedSpaces($user);

        $scope = (string)$request->get('scope', 'all');
        $scope = in_array($scope, self::SCOPES, true) ? $scope : 'all';
        $this->applyScope($query, $scope);

        if (!$request->get('archived')) {
            $query->andWhere(['!=', 'space.status', Space::STATUS_ARCHIVED]);
        }

        $keywords = trim((string)$request->get('q', ''));
        if ($keywords !== '') {
            $query->search($keywords);
        }

        $pagination = $this->handlePagination($query, 25, self::MAX_PAGE_SIZE);

        return $this->returnPagination($pagination, SpaceSerializer::batch($query->all()));
    }

    /**
     * What the caller is to the spaces they name: member, follower, and how much they have not
     * seen there yet.
     *
     * Parameter: `guids[]` — the spaces a client currently displays. Deliberately not "every
     * space of the caller": a user can be a member of a great many, while a client shows one
     * page of them. Answers `{results: {<guid>: {isMember, isFollowing, newItems}}}`.
     *
     * This is where the caller context of a space lives, which is why {@see SpaceSerializer::list()}
     * carries none of it — the same split `like/states` makes for the like state of a batch of
     * records. `newItems` is measured against the membership's last visit and is therefore `0`
     * without one.
     */
    public function actionStates()
    {
        $guids = Yii::$app->request->get('guids');
        $guids = is_array($guids)
            ? array_slice(array_filter($guids, 'is_string'), 0, self::MAX_STATE_GUIDS)
            : [];

        if ($guids === []) {
            return ['results' => (object)[]];
        }

        $userId = Yii::$app->user->id;

        $rows = Space::find()
            ->visible()
            ->filterBlockedSpaces()
            ->andWhere(['space.guid' => $guids])
            ->select([
                'guid' => 'space.guid',
                'isMember' => 'MAX(CASE WHEN membership.id IS NULL THEN 0 ELSE 1 END)',
                'isFollowing' => 'MAX(CASE WHEN follow.id IS NULL THEN 0 ELSE 1 END)',
                'newItems' => 'COUNT(content.id)',
            ])
            ->leftJoin(
                ['membership' => Membership::tableName()],
                'membership.space_id = space.id'
                . ' AND membership.user_id = :stateUser'
                . ' AND membership.status = :stateMemberStatus',
                [':stateUser' => $userId, ':stateMemberStatus' => Membership::STATUS_MEMBER],
            )
            ->leftJoin(
                ['follow' => Follow::tableName()],
                'follow.object_id = space.id'
                . ' AND follow.object_model = :stateSpaceClass'
                . ' AND follow.user_id = :stateUser',
                [':stateSpaceClass' => Space::class],
            )
            ->leftJoin(
                ['container' => ContentContainer::tableName()],
                'container.pk = space.id AND container.class = :stateSpaceClass',
            )
            // Counted through the membership join, so a space without one counts nothing -
            // "new since your last visit" has no meaning without a last visit.
            ->leftJoin(
                ['content' => Content::tableName()],
                'content.contentcontainer_id = container.id'
                . ' AND content.stream_channel = :stateChannel'
                . ' AND content.created_at > membership.last_visit',
                [':stateChannel' => 'default'],
            )
            ->groupBy('space.guid')
            ->asArray()
            ->all();

        $results = [];
        foreach ($rows as $row) {
            $results[$row['guid']] = [
                'isMember' => (bool)$row['isMember'],
                'isFollowing' => (bool)$row['isFollowing'],
                'newItems' => (int)$row['newItems'],
            ];
        }

        // (object) so an empty map serializes as `{}` rather than `[]`.
        return ['results' => $results === [] ? (object)[] : (object)$results];
    }

    /**
     * Narrows the query to the caller's own spaces and orders them the way the platform does.
     *
     * The membership is joined rather than tested with an EXISTS, because the same join carries
     * what the ordering needs (`last_visit`, and whether there is a membership at all).
     */
    private function applyScope($query, string $scope): void
    {
        if ($scope === 'all') {
            return;
        }

        $userId = Yii::$app->user->id;

        $query->leftJoin(
            ['scope_membership' => Membership::tableName()],
            'scope_membership.space_id = space.id'
            . ' AND scope_membership.user_id = :scopeUser'
            . ' AND scope_membership.status = :scopeMemberStatus',
            [':scopeUser' => $userId, ':scopeMemberStatus' => Membership::STATUS_MEMBER],
        );

        $isMember = ['not', ['scope_membership.id' => null]];
        $isFollowing = [
            'exists',
            Follow::find()
                ->where(['user_follow.user_id' => $userId, 'user_follow.object_model' => Space::class])
                ->andWhere('user_follow.object_id = space.id'),
        ];

        $query->andWhere(match ($scope) {
            'member' => $isMember,
            'following' => $isFollowing,
            default => ['or', $isMember, $isFollowing],
        });

        // Memberships first, then followed spaces - the order the space menu has always had.
        $order = [new Expression('scope_membership.id IS NULL')];

        // Mirrors Membership::findByUser(): the setting decides whether a user's spaces are
        // ordered by their own sort order or by how recently they visited them.
        if (Yii::$app->getModule('space')->settings->get('spaceOrder') == 0) {
            $order['space.sort_order'] = SORT_ASC;
            $order['space.name'] = SORT_ASC;
        } else {
            $order['scope_membership.last_visit'] = SORT_DESC;
            $order['space.name'] = SORT_ASC;
        }

        $query->orderBy($order);
    }
}
