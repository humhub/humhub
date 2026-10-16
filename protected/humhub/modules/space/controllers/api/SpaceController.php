<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers\api;

use humhub\components\api\BaseController;
use humhub\components\listing\filters\IdsFilter;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListValidationException;
use humhub\modules\content\models\Content;
use humhub\modules\content\models\ContentContainer;
use humhub\modules\space\components\SpaceList;
use humhub\modules\space\models\Membership;
use humhub\modules\space\models\Space;
use humhub\modules\space\Module;
use humhub\modules\space\serializers\MembershipSerializer;
use humhub\modules\space\serializers\SpaceSerializer;
use humhub\modules\user\models\Follow;
use Yii;
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
     * displays, not for every space a user is a member of (the list's `ids`/`exclude` have the
     * same limit, {@see IdsFilter::MAX_IDS})
     */
    public const MAX_STATE_IDS = 100;

    /**
     * @inheritdoc
     */
    protected bool $allowSessionAuth = true;

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
     * The spaces the caller may see — the space search of the platform, built by
     * {@see SpaceList}, which parses and validates the parameters; this action only pages.
     *
     * Parameters: `q` (search over name, description and tags), `scope` (`all`, `member`,
     * `following`, `mine` — memberships and followed spaces —, `none` — neither —, `archived` —
     * only archived spaces), `archived` (`1`: only archived spaces, excluded otherwise), `sort`
     * (`default`, `name`, `newest`, `oldest`; without it a scope other than `all`/`none` orders
     * the way the platform orders a user's spaces), `ids` / `exclude` (repeated or
     * comma-separated space ids, at most 100 each), `purpose` (`directory`, `picker`, `chooser`;
     * absent = neutral), the parameters of filters modules add ({@see SpaceList::EVENT_INIT}),
     * plus `page`/`pageSize` (25 by default, at most 100).
     *
     * An unknown value of one of these, and a parameter the list does not know, answers
     * `422 {errors}`.
     */
    public function actionIndex()
    {
        try {
            $spaces = (new SpaceList())->build($this->listParams(), ListContext::forCurrentUser())->query();
        } catch (ListValidationException $e) {
            return $this->validationErrors($e->errors);
        }

        $pagination = $this->handlePagination($spaces, 25, self::MAX_PAGE_SIZE);

        return $this->returnPagination($pagination, SpaceSerializer::batch($spaces->all()));
    }

    /**
     * What the caller is to the spaces they name: member, follower, and how much they have not
     * seen there yet.
     *
     * Parameter: `ids` (repeated or comma-separated) — the spaces a client currently displays. Deliberately not "every
     * space of the caller": a user can be a member of a great many, while a client shows one
     * page of them. Answers `{results: {<id>: {...}}}` with per space:
     *
     * - `isMember`, `isFollowing`, `newItems`
     * - `membership` — the shape `space/<id>/membership` answers ({@see MembershipSerializer::state()})
     * - `canViewMembers`, `canViewFollowers` — whether the member / follower lists may be opened
     * - `canFollow` — whether following can be started (not a member, following not disabled)
     * - `memberCount`, `followerCount` — the list's counts ({@see SpaceSerializer::counts()}),
     *   `null` where the space does not show them
     *
     * The page is answered with a fixed number of queries, not a number per space: the
     * memberships, follows and counts of all named spaces are loaded at once.
     *
     * This is where the caller context of a space lives, which is why {@see SpaceSerializer::list()}
     * carries none of it — the same split `like/states` makes for the like state of a batch of
     * records. `newItems` is measured against the membership's last visit and is therefore `0`
     * without one.
     */
    public function actionStates()
    {
        // Repeated (`ids[]=`) or comma-separated, like `like/states`' `recordIds`.
        $ids = Yii::$app->request->get('ids', []);
        $ids = is_array($ids) ? $ids : explode(',', (string)$ids);
        $ids = array_slice(
            array_values(array_unique(array_filter(array_map('intval', $ids), fn(int $id) => $id > 0))),
            0,
            self::MAX_STATE_IDS,
        );

        if ($ids === []) {
            return ['results' => (object)[]];
        }

        $userId = Yii::$app->user->id;

        $rows = Space::find()
            ->visible()
            ->filterBlockedSpaces()
            ->andWhere(['space.id' => $ids])
            ->select([
                'id' => 'space.id',
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
            ->groupBy('space.id')
            ->asArray()
            ->all();

        $spaces = $rows === [] ? [] : Space::find()->where(['id' => array_column($rows, 'id')])->indexBy('id')->all();
        $memberships = MembershipSerializer::states(array_values($spaces));
        $counts = SpaceSerializer::counts(array_values($spaces));
        /** @var Module $module */
        $module = Yii::$app->getModule('space');

        $results = [];
        foreach ($rows as $row) {
            $id = (int)$row['id'];
            $isMember = (bool)$row['isMember'];
            $membership = $memberships[$id];

            $results[$id] = [
                'isMember' => $isMember,
                'isFollowing' => (bool)$row['isFollowing'],
                'newItems' => (int)$row['newItems'],
                'membership' => $membership,
                // Mirrors Space::canViewMembers(): hidden members stay visible to the
                // space's privileged members. A hidden count is the list's `null`.
                'canViewMembers' => $counts[$id]['memberCount'] !== null
                    || $spaces[$id]->getMembership()?->isPrivileged() === true,
                'canViewFollowers' => $counts[$id]['followerCount'] !== null,
                // FollowSerializer::canFollow(), from what this request loaded already.
                'canFollow' => !$module->disableFollow && !$isMember,
                'memberCount' => $counts[$id]['memberCount'],
                'followerCount' => $counts[$id]['followerCount'],
            ];
        }

        // (object) so an empty map serializes as `{}` rather than `[]`, and so the numeric
        // ids stay object keys instead of turning into array indices.
        return ['results' => $results === [] ? (object)[] : (object)$results];
    }
}
