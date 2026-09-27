<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2026 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers\api;

use humhub\components\api\BaseController;
use humhub\components\listing\filters\IdsFilter;
use humhub\components\listing\filters\SearchFilter;
use humhub\components\listing\ListContext;
use humhub\components\listing\ListValidationException;
use humhub\modules\friendship\serializers\FriendshipSerializer;
use humhub\modules\user\components\UserList;
use humhub\modules\user\models\Follow;
use humhub\modules\user\models\User;
use humhub\modules\user\Module;
use humhub\modules\user\permissions\PeopleAccess;
use humhub\modules\user\serializers\UserSerializer;
use humhub\modules\user\services\IsOnlineService;
use Yii;
use yii\filters\VerbFilter;
use yii\helpers\ArrayHelper;
use yii\web\ForbiddenHttpException;

/**
 * The user list of the HTTP API (see `docs/develop/concept-api.md`).
 *
 * This is the general list of users, not one endpoint per consumer: the People directory reads
 * it, and user pickers and mentioning can read the same shape. What it answers is therefore
 * caller-NEUTRAL ({@see UserSerializer::list()}) — no "do I follow them", no friendship. A
 * caller that needs to know which of the listed users are connected to it asks for them
 * (`scope`), and a caller that needs to know what they are to it asks {@see self::actionStates()}
 * — the split the space list makes.
 *
 * Availability is never optional: every query runs through {@see UserList}, so a user the
 * caller may not see, one who blocked them or a hidden one cannot appear no matter which
 * parameters arrive. There is no guest access: the list is for logged-in users, as the People
 * directory always was.
 *
 * @since 1.20
 */
class UserController extends BaseController
{
    /**
     * @var int the largest page a client may ask for
     */
    public const MAX_PAGE_SIZE = 100;

    /**
     * @var int the most users one state request may name — a client asks for the page it
     * displays (the list's `ids`/`exclude` have the same limit, {@see IdsFilter::MAX_IDS})
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
                    'field-values' => ['GET', 'HEAD'],
                    'tags' => ['GET', 'HEAD'],
                ],
            ],
        ]);
    }

    /**
     * The users the caller may see — the user search of the platform, built by
     * {@see UserList}, which parses and validates the parameters; this action only pages.
     *
     * Parameters: `q` (search over username, the searchable profile fields and tags, at most 255
     * characters), `scope`
     * (`all`, `following`, `followers` — while following is not disabled —, `friends`,
     * `pendingFriends` — requests the caller sent, unanswered — while the friendship system is
     * on), `groupId` (members of a group shown in the directory), `tag` (repeated or
     * comma-separated user tags, at most 20: users having all of them), `spaceId` (members of a space
     * the caller may see the members of), `fields[<internal name>]` (profile fields marked as
     * directory filter), `sort` (`default` — the administrator's People order —, `firstname`,
     * `lastname`, `lastlogin`), `ids` / `exclude` (repeated or comma-separated user ids, at
     * most 100 each), `purpose` (`directory`, `picker`, `mentioning`; absent = neutral), the
     * parameters of filters modules add ({@see UserList::EVENT_INIT}), plus `page`/`pageSize`
     * (25 by default, at most 100).
     *
     * An unknown value of one of these, and a parameter the list does not know, answers
     * `422 {errors}`.
     *
     * The list is the People directory and requires its permission ({@see PeopleAccess}) whatever
     * the `purpose`. User pickers and mentioning get a rule of their own once they move to this
     * endpoint.
     *
     * @throws ForbiddenHttpException without access to the People directory
     */
    public function actionIndex()
    {
        $this->requirePeopleAccess();

        try {
            $users = (new UserList())->build($this->listParams(), ListContext::forCurrentUser())->query();
        } catch (ListValidationException $e) {
            return $this->validationErrors($e->errors);
        }

        $pagination = $this->handlePagination($users, 25, self::MAX_PAGE_SIZE);

        return $this->returnPagination($pagination, UserSerializer::batch($users->all()));
    }

    /**
     * What the caller is to the users they name — the states of the People directory's cards.
     *
     * Parameter: `ids` (repeated or comma-separated, at most 100) — the users a client
     * currently displays. Answers `{results: {<id>: {...}}}` with per user the caller may see
     * and the directory may list (hidden users, {@see User::VISIBILITY_HIDDEN}, are left out as
     * the list leaves them out — also for an administrator):
     *
     * - `isSelf` — the caller themselves
     * - `isFollowing` — whether the caller follows them
     * - `canFollow` — whether following can be started (following not disabled, not oneself)
     * - `friendship` — the shape `user/<id>/friendship` answers
     *   ({@see FriendshipSerializer::state()}), `null` while the friendship system is off and
     *   for the caller themselves (there is no friendship with oneself)
     * - `isOnline` — whether they are online, as their profile image shows it
     *   ({@see \humhub\modules\user\widgets\Image}, {@see IsOnlineService}); `null` where the
     *   online status is not shown: while the administrator hides it, for a user who hides their
     *   own, and for the caller themselves
     *
     * The page is answered with a fixed number of queries, not a number per user.
     *
     * Requires access to the People directory ({@see PeopleAccess}), as the list does.
     *
     * @throws ForbiddenHttpException without access to the People directory
     */
    public function actionStates()
    {
        $this->requirePeopleAccess();

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

        $me = (int)Yii::$app->user->id;
        /** @var User[] $users */
        $users = User::find()
            ->available()
            ->andWhere(UserList::notHidden(Yii::$app->user->getIdentity()))
            ->andWhere(['user.id' => $ids])
            ->orderBy(['user.id' => SORT_ASC])
            ->all();

        /** @var Module $module */
        $module = Yii::$app->getModule('user');
        $friendshipEnabled = (bool)Yii::$app->getModule('friendship')->settings->get('enable');

        $following = array_flip(array_map('intval', Follow::find()
            ->select('object_id')
            ->where(['user_id' => $me, 'object_model' => User::class, 'object_id' => $ids])
            ->column()));

        $others = array_values(array_filter($users, static fn(User $user) => (int)$user->id !== $me));
        $friendships = $friendshipEnabled ? FriendshipSerializer::states($others, array_keys($following)) : [];
        $online = IsOnlineService::getStatuses($others);

        $results = [];
        foreach ($users as $user) {
            $isSelf = (int)$user->id === $me;
            $results[$user->id] = [
                'isSelf' => $isSelf,
                'isFollowing' => isset($following[$user->id]),
                // FollowSerializer::canFollow(), from what this request loaded already.
                'canFollow' => !$module->disableFollow && !$isSelf,
                'friendship' => $friendships[$user->id] ?? null,
                'isOnline' => $online[$user->id] ?? null,
            ];
        }

        // (object) so an empty map serializes as `{}` rather than `[]`, and so the numeric
        // ids stay object keys instead of turning into array indices.
        return ['results' => $results === [] ? (object)[] : (object)$results];
    }

    /**
     * The values a directory filter over a profile field offers, `{results: [{id, name, count?}]}`
     * — the `optionsUrl` of the People directory's field filters.
     *
     * Parameters: `field`, the internal name of a profile field marked as directory filter, and
     * `q`, the text a picker's user typed (optional, at most {@see UserList::MAX_SEARCH_LENGTH}
     * characters — a longer one answers `422 {errors: {q}}`). The values are
     * {@see UserList::filterValues()}: `id` is what `fields[<name>]` of the list takes, `name`
     * the label, `count` (values users entered) how many users entered it. Without `q` the
     * options of a dropdown or checkbox list, else the most frequent values users entered (at
     * most {@see UserList::MAX_FILTER_VALUES}); with `q` only those containing it.
     *
     * @throws ForbiddenHttpException without access to the People directory
     */
    public function actionFieldValues()
    {
        $this->requirePeopleAccess();

        $name = Yii::$app->request->get('field');

        if ($name === null || $name === '') {
            return $this->missingParameter('field');
        }

        $field = is_string($name) ? (UserList::filterFields()[$name] ?? null) : null;
        if ($field === null) {
            return $this->validationErrors([
                'field' => [Yii::t('UserModule.base', 'Unknown value "{value}".', ['value' => is_string($name) ? $name : ''])],
            ]);
        }

        try {
            $search = $this->searchParam();
        } catch (ListValidationException $e) {
            return $this->validationErrors($e->errors);
        }

        return ['results' => (new UserList())->filterValues($field, ListContext::forCurrentUser(UserList::PURPOSE_DIRECTORY), $search)];
    }

    /**
     * The user tags of the users the caller may see, `{results: [{id, name, count}]}` — the
     * `optionsUrl` of the People directory's tag picker ({@see UserList::tagValues()}): `id` and
     * `name` are the tag, what the list's `tag` takes, `count` how many of these users have it.
     * The most frequent first, at most {@see UserList::MAX_FILTER_VALUES}.
     *
     * Parameter: `q`, the text a picker's user typed (optional, at most
     * {@see UserList::MAX_SEARCH_LENGTH} characters — a longer one answers `422 {errors: {q}}`):
     * only the tags containing it.
     *
     * Both endpoints take their values from the users the directory lists the caller, with the
     * restrictions modules apply to it ({@see UserList::EVENT_BUILD}, purpose `directory`).
     *
     * @throws ForbiddenHttpException without access to the People directory
     */
    public function actionTags()
    {
        $this->requirePeopleAccess();

        try {
            $search = $this->searchParam();
        } catch (ListValidationException $e) {
            return $this->validationErrors($e->errors);
        }

        return ['results' => (new UserList())->tagValues(ListContext::forCurrentUser(UserList::PURPOSE_DIRECTORY), $search)];
    }

    /**
     * The `q` of an `optionsUrl` request: the typed text, `null` for none (or anything but a
     * string).
     *
     * @throws ListValidationException for a text longer than the list's search takes
     */
    private function searchParam(): ?string
    {
        $q = Yii::$app->request->get('q');
        $q = is_string($q) && trim($q) !== '' ? trim($q) : null;

        if ($q !== null && mb_strlen($q) > UserList::MAX_SEARCH_LENGTH) {
            throw new ListValidationException(['q' => [SearchFilter::tooLong('q', UserList::MAX_SEARCH_LENGTH)]]);
        }

        return $q;
    }

    /**
     * @throws ForbiddenHttpException
     */
    private function requirePeopleAccess(): void
    {
        if (!Yii::$app->user->can(PeopleAccess::class)) {
            throw new ForbiddenHttpException();
        }
    }
}
