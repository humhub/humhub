<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\space\permissions\SpaceDirectoryAccess;
use Yii;

/**
 * SpacesController displays the spaces directory (the `SpaceDirectory` island)
 *
 * @since 1.9
 */
class SpacesController extends Controller
{
    /**
     * @inheritdoc
     */
    public $subLayout = '@space/views/spaces/_layout';

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->setActionTitles([
            'index' => Yii::t('SpaceModule.base', 'Spaces'),
        ]);

        parent::init();
    }

    /**
     * Action to display spaces page
     */
    public function actionIndex()
    {
        $legacyParams = $this->translateLegacyParams(Yii::$app->request->getQueryParams());
        if ($legacyParams !== null) {
            return $this->redirect(array_merge(['/space/spaces'], $legacyParams), 301);
        }

        return $this->render('index');
    }

    /**
     * The directory's parameters before 1.20 in the ones of `GET /api/v2/space` it uses now:
     * `keyword` → `q`, `connection=member|follow|none` → `scope=member|following|none`,
     * `connection=archived` → `scope=archived` (the Status select's Archived option), `sort=newer|older` → `newest|oldest` (`sortOrder`,
     * the old default, is the new one). Every other parameter is kept.
     *
     * @return array|null the translated parameters, `null` when none of the old ones is present
     * @since 1.20
     */
    private function translateLegacyParams(array $params): ?array
    {
        $legacySorts = ['sortOrder' => null, 'newer' => 'newest', 'older' => 'oldest'];
        $isLegacySort = is_string($params['sort'] ?? null) && array_key_exists($params['sort'], $legacySorts);

        if (!array_key_exists('keyword', $params) && !array_key_exists('connection', $params) && !$isLegacySort) {
            return null;
        }

        $keyword = $params['keyword'] ?? null;
        $connection = $params['connection'] ?? null;
        unset($params['keyword'], $params['connection']);

        if (is_string($keyword) && trim($keyword) !== '') {
            $params['q'] = $keyword;
        }

        $scope = match ($connection) {
            'member' => 'member',
            'follow' => 'following',
            'none' => 'none',
            default => null,
        };
        if ($scope !== null) {
            $params['scope'] = $scope;
        }
        if ($connection === 'archived') {
            $params['scope'] = 'archived';
        }

        if ($isLegacySort) {
            $sort = $legacySorts[$params['sort']];
            if ($sort === null) {
                unset($params['sort']);
            } else {
                $params['sort'] = $sort;
            }
        }

        return $params;
    }
}
