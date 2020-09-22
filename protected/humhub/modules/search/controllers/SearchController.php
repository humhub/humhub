<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2018 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\controllers;

use humhub\modules\user\widgets\Image;
use Yii;
use yii\data\Pagination;
use humhub\components\Controller;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\search\models\forms\SearchForm;
use humhub\modules\search\engine\Search;

/**
 * Search Controller provides search functions inside the application.
 *
 * @author Luke
 * @since 0.12
 */
class SearchController extends Controller
{

    /**
     * @var string the current search keyword
     */
    public static $keyword = null;

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->appendPageTitle(\Yii::t('SearchModule.base', 'Search'));
        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function getAccessRules()
    {
        return [
            ['login']
        ];
    }

    public function actionIndex()
    {
        $model = new SearchForm();
        $model->load(Yii::$app->request->get());

        $limitSpaces = [];
        if (!empty($model->limitSpaceGuids)) {
            foreach ($model->limitSpaceGuids as $guid) {
                $space = Space::findOne(['guid' => trim($guid)]);
                if ($space !== null) {
                    $limitSpaces[] = $space;
                }
            }
        }

        $options = [
            'page' => $model->page,
            'sort' => (empty($model->keyword)) ? 'title' : null,
            'pageSize' => Yii::$app->settings->get('paginationSize'),
            'limitSpaces' => $limitSpaces
        ];

        if ($model->scope == SearchForm::SCOPE_CONTENT) {
            $options['type'] = Search::DOCUMENT_TYPE_CONTENT;
        } elseif ($model->scope == SearchForm::SCOPE_SPACE) {
            $options['model'] = Space::class;
        } elseif ($model->scope == SearchForm::SCOPE_USER) {
            $options['model'] = User::class;
        } else {
            $model->scope = SearchForm::SCOPE_ALL;
        }

        $searchResultSet = Yii::$app->search->find($model->keyword, $options);

        // Store static for use in widgets (e.g. fileList)
        self::$keyword = $model->keyword;

        $pagination = new Pagination;
        $pagination->totalCount = $searchResultSet->total;
        $pagination->pageSize = $searchResultSet->pageSize;

        return $this->render('index', [
                    'model' => $model,
                    'results' => $searchResultSet->getResultInstances(),
                    'pagination' => $pagination,
                    'totals' => $model->getTotals($model->keyword, $options),
                    'limitSpaces' => $limitSpaces
        ]);
    }

}
