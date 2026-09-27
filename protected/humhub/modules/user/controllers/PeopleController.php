<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\user\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\user\components\UserList;
use humhub\modules\user\permissions\PeopleAccess;
use humhub\modules\user\widgets\PeopleFilterPicker;
use Yii;

/**
 * PeopleController displays the People directory (the `PeopleDirectory` island)
 *
 * @since 1.9
 */
class PeopleController extends Controller
{
    /**
     * @inheritdoc
     */
    public $subLayout = '@user/views/people/_layout';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setActionTitles([
            'index' => Yii::t('UserModule.base', 'People'),
        ]);

        parent::init();
    }

    /**
     * @inheritdoc
     */
    protected function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY],
            ['permissions' => [PeopleAccess::class]],
        ];
    }

    /**
     * Action to display people page
     */
    public function actionIndex()
    {
        $legacyParams = $this->translateLegacyParams(Yii::$app->request->getQueryParams());
        if ($legacyParams !== null) {
            return $this->redirect(array_merge(['/user/people'], $legacyParams), 301);
        }

        return $this->render('index');
    }

    /**
     * The directory's parameters before 1.20 in the ones of `GET /api/v2/user` it uses now:
     * `keyword` → `q`, `connection=followers|following|friends|pending_friends` →
     * `scope=followers|following|friends|pendingFriends` — left out when the feature of that
     * scope is off, so an old link does not end in a refused list. Every other parameter
     * (`groupId`, `fields[…]`, `sort`) is kept.
     *
     * @return array|null the translated parameters, `null` when none of the old ones is present
     * @since 1.20
     */
    private function translateLegacyParams(array $params): ?array
    {
        if (!array_key_exists('keyword', $params) && !array_key_exists('connection', $params)) {
            return null;
        }

        $keyword = $params['keyword'] ?? null;
        $connection = $params['connection'] ?? null;
        unset($params['keyword'], $params['connection']);

        if (is_string($keyword) && trim($keyword) !== '') {
            $params['q'] = $keyword;
        }

        $scope = match ($connection) {
            'followers' => 'followers',
            'following' => 'following',
            'friends' => 'friends',
            'pending_friends' => 'pendingFriends',
            default => null,
        };
        if ($scope !== null && in_array($scope, UserList::availableScopes(), true)) {
            $params['scope'] = $scope;
        }

        return $params;
    }

    /**
     * Returns people list in JSON format filtered by keyword - the default route of the
     * deprecated {@see PeopleFilterPicker}.
     *
     * @deprecated since 1.20, removed in 1.21 together with {@see PeopleFilterPicker}; the People
     * directory's profile field filters load their options from `GET /api/v2/user/field-values`
     */
    public function actionFilterPeopleJson($field, $keyword = null)
    {
        return $this->asJson((new PeopleFilterPicker(['itemKey' => $field]))->getSuggestions($keyword));
    }
}
