<?php
/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2021 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\space\controllers;

use humhub\components\access\ControllerAccess;
use humhub\components\Controller;
use humhub\modules\space\components\SpaceDirectoryQuery;
use humhub\modules\space\permissions\SpaceDirectoryAccess;
use humhub\modules\space\widgets\SpaceDirectoryCard;
use Yii;
use yii\helpers\Url;

/**
 * SpacesController displays users directory
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
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            [ControllerAccess::RULE_LOGGED_IN_ONLY],
            ['permissions' => [SpaceDirectoryAccess::class]],
        ];
    }

    /**
     * Action to display spaces page
     */
    public function actionIndex()
    {
        $spaceDirectoryQuery = new SpaceDirectoryQuery();

        $urlParams = Yii::$app->request->getQueryParams();
        unset($urlParams['page']);
        array_unshift($urlParams, '/space/spaces/load-more');
        $this->getView()->registerJsConfig('cards', [
            'loadMoreUrl' => Url::to($urlParams),
        ]);

        return $this->render('index', [
            'spaces' => $spaceDirectoryQuery,
        ]);
    }

    /**
     * Action to load cards for next page by AJAX
     */
    public function actionLoadMore()
    {
        $spaceQuery = new SpaceDirectoryQuery();

        $spaceCards = '';
        foreach ($spaceQuery->all() as $space) {
            $spaceCards .= SpaceDirectoryCard::widget(['space' => $space]);
        }

        return $spaceCards;
    }

}