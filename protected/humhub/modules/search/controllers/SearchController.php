<?php

/**
 * @link https://www.humhub.org/
 * @copyright Copyright (c) 2015 HumHub GmbH & Co. KG
 * @license https://www.humhub.com/licences
 */

namespace humhub\modules\search\controllers;

use Yii;
use humhub\components\Controller;
use humhub\modules\space\models\Space;
use humhub\modules\user\models\User;
use humhub\modules\search\models\forms\SearchForm;

use humhub\modules\space\widgets\Image;

/**
 * Search Controller provides search functions inside the application.
 *
 * @author Luke
 * @since 0.12
 */
class SearchController extends Controller
{
    public function init()
    {
        $this->appendPageTitle(\Yii::t('SearchModule.base', 'Search'));
        return parent::init();
    }

    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'acl' => [
                'class' => \humhub\components\behaviors\AccessControl::className(),
                'guestAllowedActions' => ['index']
            ]
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
            $options['type'] = \humhub\modules\search\engine\Search::DOCUMENT_TYPE_CONTENT;
        } elseif ($model->scope == SearchForm::SCOPE_SPACE) {
            $options['model'] = Space::className();
        } elseif ($model->scope == SearchForm::SCOPE_USER) {
            $options['model'] = User::className();
        } else {
            $model->scope = SearchForm::SCOPE_ALL;
        }

        $searchResultSet = Yii::$app->search->find($model->keyword, $options);

        $pagination = new \yii\data\Pagination;
        $pagination->totalCount = $searchResultSet->total;
        $pagination->pageSize = $searchResultSet->pageSize;

        return $this->render('index', array(
            'model' => $model,
            'results' => $searchResultSet->getResultInstances(),
            'pagination' => $pagination,
            'totals' => $model->getTotals($model->keyword, $options),
            'limitSpaces' => $limitSpaces
        ));
    }

    /**
     * JSON Search interface for Mentioning
     */
    public function actionMentioning()
    {
        \Yii::$app->response->format = 'json';

        $results = array();
        $keyword = Yii::$app->request->get('keyword', "");

        $searchResultSet = Yii::$app->search->find($keyword, [
            'model' => array(User::className(), Space::className()),
            'pageSize' => 10
        ]);

        foreach ($searchResultSet->getResultInstances() as $container) {
            $results[] = array(
                'guid' => $container->guid,
                'type' => ($container instanceof Space) ? "s" : "u",
                'name' => $container->getDisplayName(),
                'image' => ($container instanceof Space) ? Image::widget(['space' => $container, 'width' => 20]) : "<img class='img-rounded' src='" . $container->getProfileImage()->getUrl() . "' height='20' width='20' alt=''>",
                'link' => $container->getUrl()
            );
        };

        return $results;
    }
}
